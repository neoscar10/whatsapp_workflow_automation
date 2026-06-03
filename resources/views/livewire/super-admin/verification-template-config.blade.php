<div class="p-8 space-y-8 flex-1 overflow-y-auto">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">Verification Checklists</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Define country-specific and deployment-level document checklists to verify business legitimacy dynamically.</p>
        </div>
    </div>

    <!-- Alert success notifications -->
    @if (session()->has('success_templates'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
            {{ session('success_templates') }}
        </div>
    @endif
    @if (session()->has('success_documents'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
            {{ session('success_documents') }}
        </div>
    @endif

    <div class="flex flex-col gap-8">
        <!-- Verification Checklists Row -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden flex flex-col w-full">
            <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-900/50">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <span class="material-symbols-outlined text-[20px] text-primary">rule_folder</span>
                    Verification Checklists
                </h3>
                <button wire:click="openCreateTemplateModal" class="px-3.5 py-1.5 bg-primary hover:bg-primary-dark text-white text-xs font-bold rounded-lg transition-colors flex items-center gap-1">
                    <span class="material-symbols-outlined text-sm">add</span>
                    New Checklist
                </button>
            </div>

            <div class="p-5 flex flex-col gap-3">
                @forelse($templates as $tpl)
                    <div 
                        wire:click="selectTemplate('{{ $tpl->id }}')"
                        class="p-4 rounded-xl border transition-all cursor-pointer relative flex items-center justify-between gap-4 {{ $selectedTemplateId === $tpl->id ? 'bg-primary/5 dark:bg-primary/10 border-primary ring-2 ring-primary/20' : 'bg-slate-50/30 hover:bg-slate-50 dark:bg-slate-800/20 dark:hover:bg-slate-800/40 border-slate-200 dark:border-slate-800' }}"
                    >
                        <!-- Flag, Name, and Description -->
                        <div class="flex items-center gap-4 min-w-0 flex-1 pr-6">
                            <div class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-slate-100 dark:bg-slate-800 !text-black dark:!text-slate-300 font-bold text-xs uppercase">
                                {{ $tpl->country_code ?: 'GL' }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2">
                                    <p class="text-sm font-semibold truncate text-slate-900 dark:text-white">{{ $tpl->name }}</p>
                                    @if(!$tpl->is_active)
                                        <span class="bg-slate-100 dark:bg-slate-200 text-[9px] font-bold px-1.5 py-0.5 rounded uppercase" style="color: #000 !important;">Disabled</span>
                                    @endif
                                </div>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate mt-0.5">{{ $tpl->description ?: 'No description provided.' }}</p>
                            </div>
                        </div>

                        <!-- Actions Top-Right -->
                        <div class="flex items-center gap-1 shrink-0" x-data="{ open: false }" @click.stop>
                            <!-- Reorder buttons -->
                            <button wire:click="moveUpTemplate('{{ $tpl->id }}')" class="p-1 rounded text-slate-400 hover:text-slate-650 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800" title="Move Up">
                                <span class="material-symbols-outlined text-[16px]">arrow_upward</span>
                            </button>
                            <button wire:click="moveDownTemplate('{{ $tpl->id }}')" class="p-1 rounded text-slate-400 hover:text-slate-650 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800" title="Move Down">
                                <span class="material-symbols-outlined text-[16px]">arrow_downward</span>
                            </button>

                            <!-- Dropdown trigger -->
                            <button @click="open = !open" class="p-1 rounded text-slate-400 hover:text-slate-650 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800">
                                <span class="material-symbols-outlined text-[18px]">more_vert</span>
                            </button>
                            <div x-show="open" @click.outside="open = false" x-transition class="absolute right-4 mt-6 w-36 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-lg z-30 py-1 text-left" style="display: none;">
                                <button wire:click="openEditTemplateModal('{{ $tpl->id }}')" @click="open = false" class="w-full text-left px-3 py-2 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-[16px]">edit</span>
                                    <span>Edit Info</span>
                                </button>
                                <button wire:click="requestDisableTemplate('{{ $tpl->id }}')" @click="open = false" class="w-full text-left px-3 py-2 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-[16px]">{{ $tpl->is_active ? 'block' : 'check_circle' }}</span>
                                    <span>{{ $tpl->is_active ? 'Disable' : 'Enable' }}</span>
                                </button>
                                <button wire:click="deleteTemplate('{{ $tpl->id }}')" @click="open = false" class="w-full text-left px-3 py-2 text-xs font-semibold text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/20 flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-[16px] text-rose-600">delete</span>
                                    <span>Delete</span>
                                </button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="py-8 text-center text-slate-400 dark:text-slate-500 font-bold text-xs">
                        No verification checklists configured. Create one to begin.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Document Requirements Table -->
        <div class="w-full">
            @if($selectedTemplate)
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden flex flex-col">
                    <!-- Template Title Details Header -->
                    <div class="p-5 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <span class="bg-primary/10 !text-black dark:bg-primary/20 dark:!text-white text-[10px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wider">
                                    {{ $selectedTemplate->country_code ?: 'Global Requirements' }}
                                </span>
                                <h3 class="text-base font-bold text-slate-900 dark:text-white mt-1.5">{{ $selectedTemplate->name }}</h3>
                                <p class="text-xs text-slate-450 dark:text-slate-400 mt-1 leading-relaxed">{{ $selectedTemplate->description ?: 'No description provided.' }}</p>
                            </div>
                            <button wire:click="openCreateDocumentModal" class="px-3.5 py-1.5 bg-primary hover:bg-primary-dark text-white text-xs font-bold rounded-lg transition-colors flex items-center gap-1 shrink-0">
                                <span class="material-symbols-outlined text-sm">add</span>
                                Add Document
                            </button>
                        </div>
                    </div>

                    <!-- List of Documents -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-700 text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                    <th class="p-4">Sort</th>
                                    <th class="p-4">Document Details</th>
                                    <th class="p-4">Formats & Max Size</th>
                                    <th class="p-4">Rules</th>
                                    <th class="p-4 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-850 text-xs font-medium" x-data="{ draggedId: null, overId: null }">
                                @forelse($documents as $doc)
                                    <tr 
                                        class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors {{ !$doc->is_active ? 'opacity-60' : '' }}"
                                        draggable="true"
                                        @dragstart="draggedId = '{{ $doc->id }}'; $event.dataTransfer.effectAllowed = 'move'; $event.dataTransfer.setData('text/plain', '{{ $doc->id }}');"
                                        @dragover.prevent="overId = '{{ $doc->id }}'"
                                        @dragleave="if (overId === '{{ $doc->id }}') overId = null"
                                        @drop="
                                            if (draggedId && draggedId !== '{{ $doc->id }}') {
                                                $wire.reorderDocuments(draggedId, '{{ $doc->id }}');
                                            }
                                            draggedId = null;
                                            overId = null;
                                        "
                                        :class="{ 'bg-primary/5 border-t-2 border-primary': overId === '{{ $doc->id }}' }"
                                    >
                                        <!-- Sort order drag handle -->
                                        <td class="p-4 whitespace-nowrap cursor-move text-slate-400 hover:text-slate-600 dark:hover:text-slate-200" title="Drag to reorder">
                                            <div class="flex items-center">
                                                <span class="material-symbols-outlined text-[20px] select-none">drag_indicator</span>
                                            </div>
                                        </td>
                                        <td class="p-4">
                                            <p class="font-bold text-slate-900 dark:text-white text-sm">{{ $doc->name }}</p>
                                            <p class="text-[11px] text-slate-450 dark:text-slate-500 mt-0.5 max-w-sm leading-relaxed">{{ $doc->description ?: 'No description provided.' }}</p>
                                            @if($doc->placeholder)
                                                <p class="text-[10px] text-slate-400 dark:text-slate-500 italic mt-1 font-mono">Placeholder: {{ $doc->placeholder }}</p>
                                            @endif
                                        </td>
                                        <td class="p-4">
                                            <span class="font-mono text-[10px] bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded !text-black dark:!text-slate-300">{{ $doc->accepted_formats }}</span>
                                            <div class="text-[11px] text-slate-450 dark:text-slate-500 mt-1 font-bold">Max size: {{ $doc->max_size_mb }} MB</div>
                                        </td>
                                        <td class="p-4">
                                            <div class="flex flex-col gap-1">
                                                <button wire:click="toggleDocumentRequiredStatus('{{ $doc->id }}')" class="text-left">
                                                    @if($doc->is_required)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold capitalize bg-amber-100 dark:bg-amber-200" style="color: #000 !important;">
                                                            Required
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold capitalize bg-slate-100 dark:bg-slate-200" style="color: #000 !important;">
                                                            Optional
                                                        </span>
                                                    @endif
                                                </button>
                                                <button wire:click="requestDisableDocument('{{ $doc->id }}')" class="text-left">
                                                    @if($doc->is_active)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold capitalize bg-emerald-100 dark:bg-emerald-200" style="color: #000 !important;">
                                                            Active
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold capitalize bg-rose-100 dark:bg-rose-200" style="color: #000 !important;">
                                                            Disabled
                                                        </span>
                                                    @endif
                                                </button>
                                            </div>
                                        </td>
                                        <!-- Actions Dropdown or Menu -->
                                        <td class="p-4 text-right relative" x-data="{ open: false }">
                                            <div class="flex justify-end">
                                                <button @click="open = !open" class="flex items-center justify-center p-1.5 rounded-full text-slate-400 hover:text-slate-650 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800">
                                                    <span class="material-symbols-outlined text-[18px]">more_vert</span>
                                                </button>
                                            </div>
                                            <div x-show="open" @click.outside="open = false" x-transition class="absolute right-4 mt-1 w-32 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-lg z-30 py-1 text-left" style="display: none;">
                                                <button wire:click="openEditDocumentModal('{{ $doc->id }}')" @click="open = false" class="w-full text-left px-3 py-2 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 flex items-center gap-1.5">
                                                    <span class="material-symbols-outlined text-[16px]">edit</span>
                                                    <span>Edit Info</span>
                                                </button>
                                                <button wire:click="requestDisableDocument('{{ $doc->id }}')" @click="open = false" class="w-full text-left px-3 py-2 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 flex items-center gap-1.5">
                                                    <span class="material-symbols-outlined text-[16px]">{{ $doc->is_active ? 'block' : 'check_circle' }}</span>
                                                    <span>{{ $doc->is_active ? 'Disable' : 'Enable' }}</span>
                                                </button>
                                                <button wire:click="deleteDocument('{{ $doc->id }}')" @click="open = false" class="w-full text-left px-3 py-2 text-xs font-semibold text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/20 flex items-center gap-1.5">
                                                    <span class="material-symbols-outlined text-[16px] text-rose-600">delete</span>
                                                    <span>Delete</span>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="p-8 text-center text-slate-400 dark:text-slate-500 font-bold">
                                            No document requirements defined for this checklist. Click "Add Document" to start.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-12 text-center shadow-sm flex flex-col items-center justify-center min-h-[300px]">
                    <div class="w-16 h-16 bg-slate-50 dark:bg-slate-800 rounded-full flex items-center justify-center mb-6 border border-slate-100 dark:border-slate-700">
                        <span class="material-symbols-outlined text-slate-400 dark:text-slate-550 text-[36px]">rule_folder</span>
                    </div>
                    <h4 class="text-slate-900 dark:text-white font-bold text-lg mb-2">No checklist selected</h4>
                    <p class="text-slate-500 dark:text-slate-400 text-xs max-w-sm leading-relaxed">Select a verification checklist from the above list to manage its specific document requirements, or create a brand new checklist mapping.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Create/Edit Template Modal -->
    @if($showTemplateModal)
        <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-md shadow-xl overflow-hidden animate-in fade-in zoom-in-95 duration-200 flex flex-col max-h-[90vh]">
                <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center bg-slate-50 dark:bg-slate-900/50">
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">{{ $editingTemplateId ? 'Edit Checklist Info' : 'Create New Checklist' }}</h3>
                    <button wire:click="closeTemplateModal" class="text-slate-400 hover:text-slate-500 dark:hover:text-slate-300">
                        <span class="material-symbols-outlined text-[20px]">close</span>
                    </button>
                </div>
                <form wire:submit.prevent="saveTemplate" class="flex flex-col overflow-hidden">
                    <div class="p-6 space-y-4 overflow-y-auto max-h-[60vh] pr-2">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-2">Checklist Name</label>
                            <input type="text" wire:model="templateName" class="w-full h-11 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 px-4 text-sm font-medium text-slate-900 dark:text-white focus:ring-4 focus:ring-primary/10 transition-all outline-none" placeholder="e.g. Nigeria Verification Checklist" />
                            @error('templateName') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-2">Country</label>
                            <select wire:model="templateCountryCode" class="w-full h-11 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 px-4 text-sm font-medium text-slate-900 dark:text-white focus:ring-4 focus:ring-primary/10 transition-all outline-none">
                                <option value="">Global / All Countries</option>
                                @foreach(\App\Models\Company::$countries as $code => $name)
                                    <option value="{{ $code }}">{{ $name }}</option>
                                @endforeach
                            </select>
                            @error('templateCountryCode') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-2">Description</label>
                            <textarea wire:model="templateDescription" class="w-full min-h-[80px] p-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-sm font-medium text-slate-900 dark:text-white focus:ring-4 focus:ring-primary/10 transition-all outline-none resize-none" placeholder="Checklist usage and scope details..."></textarea>
                            @error('templateDescription') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-2">Sort Order</label>
                                <input type="number" min="0" wire:model="templateSortOrder" class="w-full h-11 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 px-4 text-sm font-medium text-slate-900 dark:text-white focus:ring-4 focus:ring-primary/10 transition-all outline-none" />
                                @error('templateSortOrder') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-2">Status</label>
                                <select wire:model="templateIsActive" class="w-full h-11 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 px-4 text-sm font-medium text-slate-900 dark:text-white focus:ring-4 focus:ring-primary/10 transition-all outline-none">
                                    <option value="1">Active</option>
                                    <option value="0">Disabled</option>
                                </select>
                                @error('templateIsActive') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="px-6 py-4 bg-slate-50 dark:bg-slate-900/50 border-t border-slate-100 dark:border-slate-800 flex justify-end gap-3">
                        <button type="button" wire:click="closeTemplateModal" class="px-4 py-2 text-xs font-bold bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-750 text-slate-700 dark:text-slate-300 rounded-lg transition-colors">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 text-xs font-bold bg-primary hover:bg-primary-dark text-white rounded-lg transition-colors">
                            Save Checklist
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Create/Edit Document Requirement Modal -->
    @if($showDocumentModal)
        <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-md shadow-xl overflow-hidden animate-in fade-in zoom-in-95 duration-200 flex flex-col max-h-[90vh]">
                <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center bg-slate-50 dark:bg-slate-900/50">
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">{{ $editingDocumentId ? 'Edit Document Requirement' : 'Add Document Requirement' }}</h3>
                    <button wire:click="closeDocumentModal" class="text-slate-400 hover:text-slate-500 dark:hover:text-slate-300">
                        <span class="material-symbols-outlined text-[20px]">close</span>
                    </button>
                </div>
                <form wire:submit.prevent="saveDocument" class="flex flex-col overflow-hidden">
                    <div class="p-6 space-y-4 overflow-y-auto max-h-[60vh] pr-2">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-2">Document Name</label>
                            <input type="text" wire:model="docName" class="w-full h-11 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 px-4 text-sm font-medium text-slate-900 dark:text-white focus:ring-4 focus:ring-primary/10 transition-all outline-none" placeholder="e.g. Utility Bill Address Proof" />
                            @error('docName') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-2">Description</label>
                            <textarea wire:model="docDescription" class="w-full min-h-[60px] p-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-sm font-medium text-slate-900 dark:text-white focus:ring-4 focus:ring-primary/10 transition-all outline-none resize-none" placeholder="Detailed document instructions, acceptable evidence, etc..."></textarea>
                            @error('docDescription') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-2">Placeholder / Upload Box Instruction</label>
                            <input type="text" wire:model="docPlaceholder" class="w-full h-11 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 px-4 text-sm font-medium text-slate-900 dark:text-white focus:ring-4 focus:ring-primary/10 transition-all outline-none" placeholder="e.g. Upload a clean copy of your utility bill (PDF, JPG)" />
                            @error('docPlaceholder') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-2">Accepted Formats (comma separated)</label>
                                <input type="text" wire:model="docAcceptedFormats" class="w-full h-11 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 px-4 text-sm font-medium text-slate-900 dark:text-white focus:ring-4 focus:ring-primary/10 transition-all outline-none" />
                                @error('docAcceptedFormats') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-2">Max Size (MB)</label>
                                <input type="number" min="1" max="50" wire:model="docMaxSizeMb" class="w-full h-11 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 px-4 text-sm font-medium text-slate-900 dark:text-white focus:ring-4 focus:ring-primary/10 transition-all outline-none" />
                                @error('docMaxSizeMb') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-2">Sort Order</label>
                                <input type="number" min="0" wire:model="docSortOrder" class="w-full h-11 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 px-4 text-sm font-medium text-slate-900 dark:text-white focus:ring-4 focus:ring-primary/10 transition-all outline-none" />
                                @error('docSortOrder') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-2">Requirement</label>
                                <select wire:model="docIsRequired" class="w-full h-11 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 px-4 text-sm font-medium text-slate-900 dark:text-white focus:ring-4 focus:ring-primary/10 transition-all outline-none">
                                    <option value="1">Required</option>
                                    <option value="0">Optional</option>
                                </select>
                                @error('docIsRequired') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-2">Status</label>
                                <select wire:model="docIsActive" class="w-full h-11 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 px-4 text-sm font-medium text-slate-900 dark:text-white focus:ring-4 focus:ring-primary/10 transition-all outline-none">
                                    <option value="1">Active</option>
                                    <option value="0">Disabled</option>
                                </select>
                                @error('docIsActive') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="px-6 py-4 bg-slate-50 dark:bg-slate-900/50 border-t border-slate-100 dark:border-slate-800 flex justify-end gap-3">
                        <button type="button" wire:click="closeDocumentModal" class="px-4 py-2 text-xs font-bold bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-750 text-slate-700 dark:text-slate-300 rounded-lg transition-colors">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 text-xs font-bold bg-primary hover:bg-primary-dark text-white rounded-lg transition-colors">
                            Save Requirement
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Confirm Disable Template Modal -->
    @if($confirmingDisableTemplateId)
        <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-sm shadow-xl overflow-hidden animate-in fade-in zoom-in-95 duration-200">
                <div class="p-6 text-center">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-amber-100 text-amber-600 mb-4">
                        <span class="material-symbols-outlined text-2xl">warning</span>
                    </div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white mb-2">Disable Checklist?</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed mb-6">Are you sure you want to disable this verification checklist? This will prevent new businesses from selecting or using this checklist.</p>
                    <div class="flex justify-center gap-3">
                        <button type="button" wire:click="cancelDisableTemplate" class="px-4 py-2 text-xs font-bold bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-750 text-slate-700 dark:text-slate-300 rounded-lg transition-colors">
                            Cancel
                        </button>
                        <button type="button" wire:click="confirmDisableTemplate" class="px-4 py-2 text-xs font-bold bg-amber-600 hover:bg-amber-700 text-white rounded-lg transition-colors">
                            Yes, Disable
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Confirm Disable Document Modal -->
    @if($confirmingDisableDocumentId)
        <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-sm shadow-xl overflow-hidden animate-in fade-in zoom-in-95 duration-200">
                <div class="p-6 text-center">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-amber-100 text-amber-600 mb-4">
                        <span class="material-symbols-outlined text-2xl">warning</span>
                    </div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white mb-2">Disable Document Requirement?</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed mb-6">Are you sure you want to disable this document requirement? Users will no longer be prompted to upload this document.</p>
                    <div class="flex justify-center gap-3">
                        <button type="button" wire:click="cancelDisableDocument" class="px-4 py-2 text-xs font-bold bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-750 text-slate-700 dark:text-slate-300 rounded-lg transition-colors">
                            Cancel
                        </button>
                        <button type="button" wire:click="confirmDisableDocument" class="px-4 py-2 text-xs font-bold bg-amber-650 hover:bg-amber-700 text-white rounded-lg transition-colors">
                            Yes, Disable
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
