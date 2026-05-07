<div class="flex flex-col flex-1 min-h-full bg-slate-50 dark:bg-slate-900/50">
    {{-- Header --}}
    <div class="px-8 py-8 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800">
        <div class="max-w-7xl mx-auto w-full">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <h1 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">Audience Manager</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400 font-medium mt-1">Organize contacts into tags, lists, and smart dynamic segments.</p>
                </div>
                <div class="flex items-center gap-3">
                    <button wire:click="openTagModal" class="flex items-center gap-2 px-4 py-2.5 text-sm font-black text-slate-700 bg-white border border-slate-200 rounded-2xl hover:bg-slate-50 transition-all dark:bg-slate-800 dark:text-slate-200 dark:border-slate-700">
                        <span class="material-symbols-outlined text-[20px]">label</span>
                        New Tag
                    </button>
                    <button wire:click="openStaticGroupModal" class="flex items-center gap-2 px-4 py-2.5 text-sm font-black text-slate-700 bg-white border border-slate-200 rounded-2xl hover:bg-slate-50 transition-all dark:bg-slate-800 dark:text-slate-200 dark:border-slate-700">
                        <span class="material-symbols-outlined text-[20px]">list</span>
                        New Group
                    </button>
                    <button wire:click="openDynamicSegmentModal" class="flex items-center gap-2 px-5 py-2.5 text-sm font-black text-white bg-primary rounded-2xl hover:bg-primary/90 shadow-lg shadow-primary/20 transition-all">
                        <span class="material-symbols-outlined text-[20px]">bolt</span>
                        New Segment
                    </button>
                </div>
            </div>

            {{-- Stats --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
                <div class="bg-slate-50 dark:bg-slate-800/50 p-6 rounded-3xl border border-slate-100 dark:border-slate-800 flex items-center gap-4">
                    <div class="size-12 rounded-2xl bg-blue-500/10 text-blue-500 flex items-center justify-center">
                        <span class="material-symbols-outlined text-2xl">label</span>
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Total Tags</p>
                        <p class="text-xl font-black text-slate-900 dark:text-white">{{ $stats['tags_count'] }}</p>
                    </div>
                </div>
                <div class="bg-slate-50 dark:bg-slate-800/50 p-6 rounded-3xl border border-slate-100 dark:border-slate-800 flex items-center gap-4">
                    <div class="size-12 rounded-2xl bg-indigo-500/10 text-indigo-500 flex items-center justify-center">
                        <span class="material-symbols-outlined text-2xl">list</span>
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Static Groups</p>
                        <p class="text-xl font-black text-slate-900 dark:text-white">{{ $stats['static_count'] }}</p>
                    </div>
                </div>
                <div class="bg-slate-50 dark:bg-slate-800/50 p-6 rounded-3xl border border-slate-100 dark:border-slate-800 flex items-center gap-4">
                    <div class="size-12 rounded-2xl bg-emerald-500/10 text-emerald-500 flex items-center justify-center">
                        <span class="material-symbols-outlined text-2xl">bolt</span>
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Dynamic Segments</p>
                        <p class="text-xl font-black text-slate-900 dark:text-white">{{ $stats['dynamic_count'] }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabs & Content --}}
    <div class="flex-1 flex flex-col">
        <div class="max-w-7xl mx-auto w-full px-8 py-8 flex flex-col flex-1 min-h-0">
            {{-- Tab Switcher --}}
            <div class="flex items-center gap-1 p-1 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 w-fit mb-8">
                <button wire:click="$set('activeTab', 'tags')" class="px-6 py-2 rounded-xl text-xs font-black uppercase tracking-widest transition-all {{ $activeTab === 'tags' ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'text-slate-400 hover:text-slate-600' }}">
                    Tags
                </button>
                <button wire:click="$set('activeTab', 'static_groups')" class="px-6 py-2 rounded-xl text-xs font-black uppercase tracking-widest transition-all {{ $activeTab === 'static_groups' ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'text-slate-400 hover:text-slate-600' }}">
                    Static Groups
                </button>
                <button wire:click="$set('activeTab', 'dynamic_segments')" class="px-6 py-2 rounded-xl text-xs font-black uppercase tracking-widest transition-all {{ $activeTab === 'dynamic_segments' ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'text-slate-400 hover:text-slate-600' }}">
                    Dynamic Segments
                </button>
            </div>

            {{-- Main List Container --}}
            <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] border border-slate-200 dark:border-slate-800 shadow-xl overflow-hidden flex flex-col flex-1 min-h-[500px]">
                {{-- Search Bar --}}
                <div class="px-8 py-6 border-b border-slate-100 dark:border-slate-800">
                    <div class="relative max-w-md">
                        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">search</span>
                        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search {{ str_replace('_', ' ', $activeTab) }}..." class="w-full pl-12 pr-4 py-3 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl text-sm focus:ring-2 focus:ring-primary/20 transition-all">
                    </div>
                </div>

                {{-- Table --}}
                <div class="flex-1 overflow-y-auto no-scrollbar pb-6">
                    <table class="w-full text-left">
                        <thead class="sticky top-0 bg-white dark:bg-slate-900 z-10">
                            <tr>
                                <th class="px-8 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400 border-b border-slate-100 dark:border-slate-800">Name</th>
                                <th class="px-8 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400 border-b border-slate-100 dark:border-slate-800">Description</th>
                                <th class="px-8 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400 border-b border-slate-100 dark:border-slate-800">Matched Contacts</th>
                                <th class="px-8 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400 border-b border-slate-100 dark:border-slate-800 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50 dark:divide-slate-800/50">
                            @forelse($items as $item)
                                <tr class="group hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-all">
                                    <td class="px-8 py-5">
                                        <div class="flex items-center gap-3">
                                            @if($activeTab === 'tags')
                                                <div class="size-4 rounded-full shadow-sm" style="background-color: {{ $item->color ?? '#3b82f6' }}"></div>
                                            @endif
                                            <span class="text-sm font-black text-slate-900 dark:text-white">{{ $item->name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-8 py-5">
                                        <p class="text-xs text-slate-500 dark:text-slate-400 line-clamp-1">{{ $item->description ?: 'No description provided' }}</p>
                                    </td>
                                    <td class="px-8 py-5">
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-black text-slate-700 dark:text-slate-300">
                                                {{ number_format($item->resolved_count ?? $item->contacts_count ?? 0) }}
                                            </span>
                                            <span class="text-[10px] font-bold text-slate-400 uppercase">Contacts</span>
                                        </div>
                                    </td>
                                    <td class="px-8 py-5 text-right">
                                        <div class="flex items-center justify-end gap-1">
                                            <button wire:click="open{{ $activeTab === 'tags' ? 'Tag' : ($activeTab === 'static_groups' ? 'StaticGroup' : 'DynamicSegment') }}Modal({{ $item->id }})" class="p-2 text-slate-400 hover:text-primary hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-all">
                                                <span class="material-symbols-outlined text-[20px]">edit</span>
                                            </button>
                                            <button wire:click="delete{{ $activeTab === 'tags' ? 'Tag' : ($activeTab === 'static_groups' ? 'Group' : 'Group') }}({{ $item->id }})" wire:confirm="Are you sure you want to delete this {{ str_replace('_', ' ', $activeTab) }}? Contacts will not be deleted." class="p-2 text-slate-400 hover:text-rose-500 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-all">
                                                <span class="material-symbols-outlined text-[20px]">delete</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-8 py-20 text-center">
                                        <div class="flex flex-col items-center gap-4">
                                            <div class="size-16 rounded-full bg-slate-50 dark:bg-slate-800 flex items-center justify-center text-slate-300">
                                                <span class="material-symbols-outlined text-[40px]">group_off</span>
                                            </div>
                                            <div>
                                                <p class="text-sm font-black text-slate-900 dark:text-white">No {{ str_replace('_', ' ', $activeTab) }} found</p>
                                                <p class="text-xs text-slate-500 mt-1">Start by creating a new audience segment to target your customers.</p>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Modals --}}

    {{-- Tag Modal --}}
    @if($showTagModal)
        <div class="fixed inset-0 z-[110] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
            <div class="bg-white dark:bg-slate-900 w-full max-w-lg rounded-[2.5rem] shadow-2xl overflow-hidden flex flex-col relative z-20">
                <div class="px-8 py-6 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <h2 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">{{ $selectedId ? 'Edit Tag' : 'Create New Tag' }}</h2>
                    <button wire:click="$set('showTagModal', false)" class="text-slate-400 hover:text-slate-600 transition-colors"><span class="material-symbols-outlined">close</span></button>
                </div>
                <div class="p-8 space-y-6">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase tracking-widest text-slate-400">Tag Name</label>
                        <input wire:model="name" type="text" placeholder="e.g. Premium Customer" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl text-sm focus:ring-2 focus:ring-primary/20 transition-all">
                        @error('name') <p class="text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase tracking-widest text-slate-400">Color</label>
                        <div class="flex items-center gap-3">
                            <input wire:model="color" type="color" class="size-10 rounded-xl cursor-pointer border-none p-0 bg-transparent overflow-hidden">
                            <input wire:model="color" type="text" placeholder="#000000" class="flex-1 px-4 py-3 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl text-sm focus:ring-2 focus:ring-primary/20 transition-all uppercase">
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase tracking-widest text-slate-400">Description</label>
                        <textarea wire:model="description" rows="3" placeholder="What is this tag for?" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl text-sm focus:ring-2 focus:ring-primary/20 transition-all"></textarea>
                    </div>
                </div>
                <div class="px-8 py-6 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-100 dark:border-slate-800 flex justify-end gap-3">
                    <button wire:click="$set('showTagModal', false)" class="px-6 py-2.5 text-xs font-black uppercase tracking-widest text-slate-400 hover:text-slate-600 transition-all">Cancel</button>
                    <button wire:click="saveTag" class="px-8 py-2.5 text-xs font-black uppercase tracking-widest text-white bg-primary rounded-2xl shadow-lg shadow-primary/20 hover:scale-[1.02] active:scale-[0.98] transition-all">Save Tag</button>
                </div>
            </div>
        </div>
    @endif

    {{-- Static Group Modal --}}
    @if($showStaticGroupModal)
        <div class="fixed inset-0 z-[110] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
            <div class="bg-white dark:bg-slate-900 w-full max-w-lg rounded-[2.5rem] shadow-2xl overflow-hidden flex flex-col relative z-20">
                <div class="px-8 py-6 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <h2 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">{{ $selectedId ? 'Edit Group' : 'Create Static Group' }}</h2>
                    <button wire:click="$set('showStaticGroupModal', false)" class="text-slate-400 hover:text-slate-600 transition-colors"><span class="material-symbols-outlined">close</span></button>
                </div>
                <div class="p-8 space-y-6">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase tracking-widest text-slate-400">Group Name</label>
                        <input wire:model="name" type="text" placeholder="e.g. VIP List" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl text-sm focus:ring-2 focus:ring-primary/20 transition-all">
                        @error('name') <p class="text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase tracking-widest text-slate-400">Description</label>
                        <textarea wire:model="description" rows="3" placeholder="What kind of contacts are in this list?" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl text-sm focus:ring-2 focus:ring-primary/20 transition-all"></textarea>
                    </div>
                    <div class="p-4 bg-amber-50 rounded-2xl border border-amber-100 flex gap-3">
                        <span class="material-symbols-outlined text-amber-500">info</span>
                        <p class="text-[10px] font-bold text-amber-700 leading-relaxed uppercase tracking-wider">Static groups are manual lists. You can add or remove contacts from this group individually.</p>
                    </div>
                </div>
                <div class="px-8 py-6 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-100 dark:border-slate-800 flex justify-end gap-3">
                    <button wire:click="$set('showStaticGroupModal', false)" class="px-6 py-2.5 text-xs font-black uppercase tracking-widest text-slate-400 hover:text-slate-600 transition-all">Cancel</button>
                    <button wire:click="saveStaticGroup" class="px-8 py-2.5 text-xs font-black uppercase tracking-widest text-white bg-primary rounded-2xl shadow-lg shadow-primary/20 hover:scale-[1.02] active:scale-[0.98] transition-all">Save Group</button>
                </div>
            </div>
        </div>
    @endif

    {{-- Dynamic Segment Modal (Rule Builder) --}}
    @if($showDynamicSegmentModal)
        <div class="fixed inset-0 z-[110] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm overflow-y-auto">
            <div class="bg-white dark:bg-slate-900 w-full max-w-4xl rounded-[2.5rem] shadow-2xl overflow-hidden flex flex-col relative z-20 my-auto">
                <div class="px-8 py-6 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">{{ $selectedId ? 'Edit Segment' : 'Create Dynamic Segment' }}</h2>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Contacts will be automatically added/removed based on these rules.</p>
                    </div>
                    <button wire:click="$set('showDynamicSegmentModal', false)" class="text-slate-400 hover:text-slate-600 transition-colors"><span class="material-symbols-outlined">close</span></button>
                </div>
                <div class="p-8 space-y-8 max-h-[60vh] overflow-y-auto no-scrollbar">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase tracking-widest text-slate-400">Segment Name</label>
                            <input wire:model="name" type="text" placeholder="e.g. High Engagement Leads" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl text-sm focus:ring-2 focus:ring-primary/20 transition-all">
                            @error('name') <p class="text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase tracking-widest text-slate-400">Description</label>
                            <input wire:model="description" type="text" placeholder="Optional context..." class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl text-sm focus:ring-2 focus:ring-primary/20 transition-all">
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Match Logic:</span>
                                <select wire:model="rules.match" class="px-4 py-1.5 bg-slate-100 dark:bg-slate-800 border-none rounded-xl text-[10px] font-black uppercase tracking-widest focus:ring-2 focus:ring-primary/20 transition-all">
                                    <option value="all">Match ALL Conditions</option>
                                    <option value="any">Match ANY Condition</option>
                                </select>
                            </div>
                            <button wire:click="addCondition" class="text-[10px] font-black uppercase tracking-widest text-primary hover:text-primary/80 transition-all flex items-center gap-1">
                                <span class="material-symbols-outlined text-sm">add</span>
                                Add Condition
                            </button>
                        </div>

                        <div class="space-y-3">
                            @foreach($rules['conditions'] as $index => $condition)
                                <div class="flex flex-wrap items-center gap-3 p-4 bg-slate-50 dark:bg-slate-800/50 rounded-2xl border border-slate-100 dark:border-slate-800 animate-in fade-in slide-in-from-left-4">
                                    <select wire:model.live="rules.conditions.{{ $index }}.field" class="flex-1 min-w-[150px] px-4 py-2.5 bg-white dark:bg-slate-900 border-none rounded-xl text-xs font-bold focus:ring-2 focus:ring-primary/20 transition-all">
                                        <option value="">Select Field</option>
                                        @foreach($availableFields as $key => $field)
                                            <option value="{{ $key }}">{{ $field['label'] }}</option>
                                        @endforeach
                                    </select>

                                    @if($fieldKey = $rules['conditions'][$index]['field'])
                                        @php $fieldType = $availableFields[$fieldKey]['type']; @endphp
                                        <select wire:model.live="rules.conditions.{{ $index }}.operator" class="flex-1 min-w-[120px] px-4 py-2.5 bg-white dark:bg-slate-900 border-none rounded-xl text-xs font-bold focus:ring-2 focus:ring-primary/20 transition-all">
                                            <option value="">Operator</option>
                                            @foreach(app(\App\Services\Contact\ContactSegmentRuleService::class)->availableOperatorsForType($fieldType) as $op)
                                                <option value="{{ $op['value'] }}">{{ $op['label'] }}</option>
                                            @endforeach
                                        </select>

                                        @if(!in_array($rules['conditions'][$index]['operator'], ['is_empty', 'is_not_empty']))
                                            <div class="flex-[2] min-w-[200px]">
                                                @if($fieldType === 'select')
                                                    <select wire:model="rules.conditions.{{ $index }}.value" class="w-full px-4 py-2.5 bg-white dark:bg-slate-900 border-none rounded-xl text-xs font-bold focus:ring-2 focus:ring-primary/20 transition-all">
                                                        <option value="">Select Value</option>
                                                        @foreach($availableFields[$fieldKey]['options'] as $opt)
                                                            <option value="{{ $opt['value'] }}">{{ $opt['label'] }}</option>
                                                        @endforeach
                                                    </select>
                                                @elseif($fieldType === 'boolean')
                                                    <select wire:model="rules.conditions.{{ $index }}.value" class="w-full px-4 py-2.5 bg-white dark:bg-slate-900 border-none rounded-xl text-xs font-bold focus:ring-2 focus:ring-primary/20 transition-all">
                                                        <option value="1">Yes</option>
                                                        <option value="0">No</option>
                                                    </select>
                                                @elseif($fieldType === 'multiselect_tags')
                                                    {{-- Multi-select tag input here - Simplified for now --}}
                                                    <input type="text" wire:model="rules.conditions.{{ $index }}.value" placeholder="Comma separated IDs" class="w-full px-4 py-2.5 bg-white dark:bg-slate-900 border-none rounded-xl text-xs font-bold focus:ring-2 focus:ring-primary/20 transition-all">
                                                @else
                                                    <input type="text" wire:model="rules.conditions.{{ $index }}.value" placeholder="Value" class="w-full px-4 py-2.5 bg-white dark:bg-slate-900 border-none rounded-xl text-xs font-bold focus:ring-2 focus:ring-primary/20 transition-all">
                                                @endif
                                            </div>
                                        @endif
                                    @endif

                                    <button wire:click="removeCondition({{ $index }})" class="p-2 text-slate-400 hover:text-rose-500 transition-colors">
                                        <span class="material-symbols-outlined">delete</span>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="px-8 py-6 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <button wire:click="previewSegment" class="flex items-center gap-2 text-xs font-black uppercase tracking-widest text-slate-500 hover:text-slate-700 transition-all">
                        <span class="material-symbols-outlined text-sm">visibility</span>
                        Preview Matches
                    </button>
                    <div class="flex gap-3">
                        <button wire:click="$set('showDynamicSegmentModal', false)" class="px-6 py-2.5 text-xs font-black uppercase tracking-widest text-slate-400 hover:text-slate-600 transition-all">Cancel</button>
                        <button wire:click="saveDynamicSegment" class="px-8 py-2.5 text-xs font-black uppercase tracking-widest text-white bg-primary rounded-2xl shadow-lg shadow-primary/20 hover:scale-[1.02] active:scale-[0.98] transition-all">Save Segment</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Preview Modal --}}
    @if($showPreviewModal)
        <div class="fixed inset-0 z-[120] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
            <div class="bg-white dark:bg-slate-900 w-full max-w-2xl rounded-[2.5rem] shadow-2xl overflow-hidden flex flex-col relative z-30">
                <div class="px-8 py-6 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">Segment Preview</h2>
                        <p class="text-[10px] font-bold text-emerald-600 uppercase tracking-widest mt-1">Matched {{ number_format($previewResults['total_count']) }} Contacts</p>
                    </div>
                    <button wire:click="$set('showPreviewModal', false)" class="text-slate-400 hover:text-slate-600 transition-colors"><span class="material-symbols-outlined">close</span></button>
                </div>
                <div class="p-8 max-h-[50vh] overflow-y-auto no-scrollbar">
                    <table class="w-full text-left">
                        <thead class="sticky top-0 bg-white dark:bg-slate-900 z-10">
                            <tr>
                                <th class="pb-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Contact</th>
                                <th class="pb-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Phone</th>
                                <th class="pb-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50 dark:divide-slate-800/50">
                            @foreach($previewResults['sample'] as $contact)
                                <tr>
                                    <td class="py-3 text-sm font-bold text-slate-700 dark:text-slate-300">{{ $contact->name ?: 'Unknown' }}</td>
                                    <td class="py-3 text-xs text-slate-500">{{ $contact->phone }}</td>
                                    <td class="py-3">
                                        <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-widest bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400">{{ $contact->status }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-8 py-6 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-100 dark:border-slate-800 flex justify-end">
                    <button wire:click="$set('showPreviewModal', false)" class="px-8 py-2.5 text-xs font-black uppercase tracking-widest text-white bg-slate-900 dark:bg-primary rounded-2xl transition-all">Close Preview</button>
                </div>
            </div>
        </div>
    @endif
</div>
