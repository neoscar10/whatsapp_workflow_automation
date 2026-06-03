<div class="mx-auto w-full max-w-5xl p-8 space-y-8 flex-1 overflow-y-auto">
    <style>
        .dark input[type="date"]::-webkit-calendar-picker-indicator {
            filter: invert(1) brightness(1.5);
        }
    </style>
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white">Business Verification</h1>
            <p class="mt-2 text-slate-550 dark:text-slate-400">
                Verify your organization's legitimacy to unlock advanced business onboarding features and verified badges.
            </p>
        </div>
    </div>

    <!-- Session Messages -->
    @if (session()->has('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    <!-- Verification Overview Card -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Status & Progress -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm flex flex-col justify-between col-span-2">
            <div class="flex items-center justify-between mb-4">
                <div class="space-y-1">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Current Status</span>
                    <div class="flex items-center gap-2">
                        @php
                            $statusMap = [
                                'not_started' => ['label' => 'Not Started', 'color' => 'bg-slate-500 text-white'],
                                'in_progress' => ['label' => 'In Progress', 'color' => 'bg-amber-600 text-white'],
                                'under_review' => ['label' => 'Under Review', 'color' => 'bg-blue-600 text-white'],
                                'partially_approved' => ['label' => 'Partially Approved', 'color' => 'bg-indigo-600 text-white'],
                                'verified' => ['label' => 'Verified Business', 'color' => 'bg-emerald-600 text-white'],
                                'rejected' => ['label' => 'Rejection / Action Needed', 'color' => 'bg-rose-600 text-white'],
                                'expired' => ['label' => 'Expired', 'color' => 'bg-red-600 text-white'],
                                'suspended' => ['label' => 'Suspended', 'color' => 'bg-slate-950 text-white'],
                            ];
                            $curr = $statusMap[$verification->status] ?? ['label' => 'Unknown', 'color' => 'bg-slate-100 text-slate-750'];
                        @endphp
                        <span class="px-3 py-1 rounded-full text-xs font-bold tracking-wide uppercase {{ $curr['color'] }}">
                            {{ $curr['label'] }}
                        </span>
                        @if($verification->status === 'verified')
                            <span class="material-symbols-outlined text-primary text-[20px]" title="Trusted Business">verified</span>
                        @endif
                    </div>
                </div>

                <div class="text-right">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Completion</span>
                    <span class="text-2xl font-bold text-slate-900 dark:text-white">{{ $verification->progress_percentage }}%</span>
                </div>
            </div>

            <!-- Progress Bar -->
            <div class="space-y-2">
                <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-2">
                    <div class="bg-primary h-2 rounded-full transition-all duration-500" style="width: {{ $verification->progress_percentage }}%"></div>
                </div>
                <p class="text-[11px] text-slate-450 dark:text-slate-500">
                    All required checklist documents must be uploaded and approved to acquire verification status.
                </p>
            </div>
        </div>

        <!-- Metrics Cards -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm grid grid-cols-3 gap-2 text-center">
            <div class="flex flex-col justify-center space-y-1">
                <span class="text-[10px] font-bold text-slate-400 uppercase">Approved</span>
                <span class="text-xl font-bold text-emerald-600 dark:text-emerald-400">{{ $approvedCount }}</span>
            </div>
            <div class="flex flex-col justify-center space-y-1 border-x border-slate-100 dark:border-slate-800">
                <span class="text-[10px] font-bold text-slate-400 uppercase">Pending</span>
                <span class="text-xl font-bold text-blue-600 dark:text-blue-400">{{ $pendingCount }}</span>
            </div>
            <div class="flex flex-col justify-center space-y-1">
                <span class="text-[10px] font-bold text-slate-400 uppercase">Rejected</span>
                <span class="text-xl font-bold text-rose-600 dark:text-rose-450">{{ $rejectedCount }}</span>
            </div>
        </div>
    </div>

    <!-- Checklist grid -->
    <div class="space-y-4">
        <h3 class="text-lg font-bold text-slate-900 dark:text-white">Document Requirements</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @forelse($verification->documents as $doc)
                @php
                    $latest = $doc->latestVersion;
                    $statusStyles = [
                        'not_submitted' => ['bg' => 'bg-slate-50 dark:bg-slate-800/20 border-slate-200 dark:border-slate-800', 'badge' => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-400', 'label' => 'Not Submitted'],
                        'pending_review' => ['bg' => 'bg-blue-50/20 dark:bg-blue-950/10 border-blue-200 dark:border-blue-900', 'badge' => 'bg-blue-600 text-white dark:bg-blue-600 dark:text-white', 'label' => 'Pending Review'],
                        'approved' => ['bg' => 'bg-emerald-50/25 dark:bg-emerald-950/5 border-emerald-250 dark:border-emerald-900', 'badge' => 'bg-emerald-600 text-white dark:bg-emerald-600 dark:text-white', 'label' => 'Approved'],
                        'rejected' => ['bg' => 'bg-rose-50/30 dark:bg-rose-950/5 border-rose-250 dark:border-rose-900', 'badge' => 'bg-rose-600 text-white dark:bg-rose-600 dark:text-white', 'label' => 'Rejected'],
                        'resubmission_required' => ['bg' => 'bg-amber-50/30 dark:bg-amber-950/5 border-amber-250 dark:border-amber-900', 'badge' => 'bg-amber-600 text-white dark:bg-amber-600 dark:text-white', 'label' => 'Resubmission Required'],
                    ];
                    
                    // Resolve actual status
                    $stateKey = $doc->status;
                    if ($stateKey === 'rejected' && $latest) {
                        $stateKey = 'resubmission_required';
                    }
                    
                    $styles = $statusStyles[$stateKey] ?? $statusStyles['not_submitted'];
                @endphp
                <div class="border rounded-2xl p-5 flex flex-col justify-between transition-all {{ $styles['bg'] }}">
                    <div>
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <span class="px-2 py-0.5 text-[9px] font-bold rounded uppercase tracking-wider {{ $doc->documentType->is_required ? 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-400' : 'bg-slate-100 text-slate-650 dark:bg-slate-800 dark:text-slate-400' }}">
                                    {{ $doc->documentType->is_required ? 'Required' : 'Optional' }}
                                </span>
                                <h4 class="font-bold text-slate-900 dark:text-white mt-2 text-sm">{{ $doc->documentType->name }}</h4>
                                <p class="text-xs text-slate-500 dark:text-slate-450 mt-1 leading-relaxed">{{ $doc->documentType->description }}</p>
                            </div>
                            <span class="px-2.5 py-0.5 rounded text-[10px] font-bold tracking-wide uppercase shrink-0 {{ $styles['badge'] }}">
                                {{ $styles['label'] }}
                            </span>
                        </div>

                        <!-- Reviewer Feedback / Reason -->
                        @if($latest && $latest->status === 'rejected')
                            <div class="mt-4 p-3 rounded-xl bg-rose-50 dark:bg-rose-950/20 border border-rose-100 dark:border-rose-900/50">
                                <p class="text-xs font-bold text-rose-800 dark:text-rose-400">Rejection Reason: {{ ucwords(str_replace('_', ' ', $latest->rejection_reason)) }}</p>
                                @if($latest->reviewer_notes)
                                    <p class="text-[11px] text-rose-700 dark:text-rose-450 mt-1 italic">Note: "{{ $latest->reviewer_notes }}"</p>
                                @endif
                            </div>
                        @elseif($latest && $latest->status === 'approved' && $latest->reviewer_notes)
                            <div class="mt-4 p-3 rounded-xl bg-emerald-50/40 dark:bg-emerald-950/10 border border-emerald-100 dark:border-emerald-900/50">
                                <p class="text-[11px] text-emerald-800 dark:text-emerald-400 italic">Reviewer Note: "{{ $latest->reviewer_notes }}"</p>
                            </div>
                        @endif

                        <!-- File Details if Uploaded -->
                        @if($latest)
                            <div class="mt-4 flex items-center justify-between text-xs text-slate-450 bg-slate-50 dark:bg-slate-800/30 p-2.5 rounded-xl border border-slate-100 dark:border-slate-800 font-mono">
                                <span class="truncate pr-4" title="{{ $latest->file_name }}">{{ $latest->file_name }}</span>
                                <span class="shrink-0">v{{ $latest->version_number }}</span>
                            </div>
                        @endif
                    </div>

                    <!-- Card Actions -->
                    <div class="mt-5 pt-4 border-t border-slate-100 dark:border-slate-800/60 flex items-center justify-between gap-3">
                        <div>
                            @if($latest)
                                <button type="button" wire:click="openHistoryModal('{{ $doc->documentType->id }}')" class="text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 text-xs font-semibold flex items-center gap-1">
                                    <span class="material-symbols-outlined text-sm">history</span>
                                    <span>Version History</span>
                                </button>
                            @endif
                        </div>

                        <div>
                            @if($stateKey === 'not_submitted')
                                <button type="button" wire:click="openUploadModal('{{ $doc->documentType->id }}')" class="px-3.5 py-1.5 bg-primary hover:bg-primary/95 text-white text-xs font-bold rounded-lg transition-all flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-[16px]">upload</span>
                                    <span>Upload Document</span>
                                </button>
                            @elseif($stateKey === 'resubmission_required' || $stateKey === 'rejected')
                                <button type="button" wire:click="openUploadModal('{{ $doc->documentType->id }}')" class="px-3.5 py-1.5 bg-rose-650 hover:bg-rose-700 text-white text-xs font-bold rounded-lg transition-all flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-[16px]">replay</span>
                                    <span>Upload New Version</span>
                                </button>
                            @elseif($stateKey === 'approved' || $stateKey === 'pending_review')
                                <a href="{{ $latest->getDownloadUrl() }}" target="_blank" class="px-3.5 py-1.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-750 text-slate-750 dark:text-slate-300 text-xs font-bold rounded-lg transition-all flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-[16px]">visibility</span>
                                    <span>View Uploaded</span>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-2 py-12 text-center text-slate-400 dark:text-slate-500 font-bold bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm flex flex-col items-center justify-center">
                    <span class="material-symbols-outlined text-[36px] text-slate-350">domain_disabled</span>
                    <p class="mt-2 text-xs">No active verification checklist mapping found for your company's country ({{ Auth::user()->company->country }}).</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Timeline / Activity Log -->
    @if($verification->timeline->isNotEmpty())
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm space-y-4">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">Activity Timeline</h3>
            <div class="relative border-l border-slate-100 dark:border-slate-800 space-y-6" style="padding-left: 40px;">
                @foreach($verification->timeline as $time)
                    <div class="relative">
                        <!-- Icon Circle -->
                        <span class="absolute flex size-6 items-center justify-center rounded-full bg-white dark:bg-slate-900 border-2 border-slate-200 dark:border-slate-700" style="left: -52px; top: 2px;">
                            @if($time->event_type === 'upload')
                                <span class="material-symbols-outlined text-xs text-primary font-bold">upload</span>
                            @elseif($time->event_type === 'approve_doc')
                                <span class="material-symbols-outlined text-xs text-emerald-600 font-bold">check</span>
                            @elseif($time->event_type === 'reject_doc')
                                <span class="material-symbols-outlined text-xs text-rose-600 font-bold">close</span>
                            @else
                                <span class="material-symbols-outlined text-xs text-slate-500 font-bold">info</span>
                            @endif
                        </span>
                        <div>
                            <div class="flex items-center gap-3">
                                <span class="text-xs font-bold text-slate-900 dark:text-white">{{ $time->title }}</span>
                                <span class="text-[10px] text-slate-400">{{ $time->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="text-xs text-slate-450 mt-1 leading-relaxed">{{ $time->description }}</p>
                            @if($time->actor)
                                <p class="text-[10px] text-slate-400 mt-1">Performed by: {{ $time->actor->name }}</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Upload Modal -->
    @if($showUploadModal)
        @php
            $docType = \App\Models\DocumentType::find($selectedDocTypeId);
        @endphp
        <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-md shadow-xl overflow-hidden animate-in fade-in zoom-in-95 duration-200 flex flex-col max-h-[90vh]">
                <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center bg-slate-50 dark:bg-slate-900/50">
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Upload {{ $docType ? $docType->name : 'Document' }}</h3>
                    <button wire:click="closeUploadModal" class="text-slate-400 hover:text-slate-500 dark:hover:text-slate-300">
                        <span class="material-symbols-outlined text-[20px]">close</span>
                    </button>
                </div>
                <form wire:submit.prevent="submitDocument" class="flex flex-col overflow-hidden">
                    <div class="p-6 space-y-4 overflow-y-auto max-h-[60vh] pr-2">
                        <!-- Instructions -->
                        <div class="p-3 bg-slate-50 dark:bg-slate-800/40 rounded-xl text-xs text-slate-500 leading-relaxed border border-slate-100 dark:border-slate-800">
                            <strong>Requirements:</strong> Format ({{ $docType->accepted_formats }}) up to {{ $docType->max_size_mb }}MB.
                        </div>

                        <!-- Drag and Drop upload area -->
                        <div 
                            class="relative flex flex-col items-center justify-center border-2 border-dashed border-slate-200 dark:border-slate-700 rounded-2xl p-6 text-center cursor-pointer bg-slate-50/50 hover:bg-slate-50 dark:bg-slate-900 dark:hover:bg-slate-850/40 transition-colors"
                            x-data="{ isDragging: false, isUploading: false, progress: 0 }"
                            @dragover.prevent="isDragging = true"
                            @dragleave.prevent="isDragging = false"
                            @drop.prevent="isDragging = false; $wire.upload('file', $event.dataTransfer.files[0])"
                            x-on:livewire-upload-start="isUploading = true"
                            x-on:livewire-upload-finish="isUploading = false"
                            x-on:livewire-upload-error="isUploading = false"
                            x-on:livewire-upload-progress="progress = $event.detail.progress"
                            onclick="document.getElementById('doc_file_input').click()"
                        >
                            <input type="file" id="doc_file_input" class="hidden" wire:model="file" accept="{{ '.' . str_replace(',', ',.', $docType->accepted_formats) }}" />
                            <span class="material-symbols-outlined text-[36px] text-slate-400 mb-2">upload_file</span>
                            <p class="text-xs font-bold text-slate-750 dark:text-slate-350">Click or drag and drop here</p>
                            <p class="text-[10px] text-slate-400 mt-1">Accepts PDF, JPG, PNG files</p>
 
                            <!-- Livewire Upload Progress -->
                            <div class="w-full mt-3" x-show="isUploading" x-cloak>
                                <div class="w-full bg-slate-200 dark:bg-slate-700 h-1.5 rounded-full overflow-hidden">
                                    <div class="bg-primary h-1.5 rounded-full transition-all duration-300" x-bind:style="'width: ' + progress + '%'"></div>
                                </div>
                                <span class="text-[9px] text-primary font-bold mt-1 block" x-text="'Uploading file... ' + progress + '%'"></span>
                            </div>
                        </div>
                        @error('file') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror

                        <!-- Preview before upload -->
                        @if($file)
                            <div class="p-3 bg-slate-50 dark:bg-slate-850 rounded-xl border border-slate-100 dark:border-slate-800 flex items-center justify-between">
                                <div class="flex items-center gap-3 truncate">
                                    @if(in_array(strtolower($file->getClientOriginalExtension()), ['jpg', 'png', 'jpeg', 'gif']))
                                        <img src="{{ $file->temporaryUrl() }}" class="size-10 object-cover rounded border border-slate-250" />
                                    @else
                                        <span class="material-symbols-outlined text-[30px] text-slate-400">description</span>
                                    @endif
                                    <div class="truncate">
                                        <p class="text-xs font-bold text-slate-750 dark:text-slate-300 truncate">{{ $file->getClientOriginalName() }}</p>
                                        <p class="text-[10px] text-slate-400">{{ round($file->getSize() / 1024, 1) }} KB</p>
                                    </div>
                                </div>
                                <button type="button" wire:click="$set('file', null)" class="text-rose-600 hover:text-rose-700">
                                    <span class="material-symbols-outlined text-[18px]">delete</span>
                                </button>
                            </div>
                        @endif

                        <!-- Dates -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-550 dark:text-slate-400 uppercase mb-2">Issue Date (Optional)</label>
                                <input type="date" wire:model="issueDate" class="w-full h-11 rounded-xl border border-slate-200 dark:border-slate-750 bg-slate-50 dark:bg-slate-900 px-4 text-sm text-slate-900 dark:text-white outline-none focus:ring-4 focus:ring-primary/10 transition-all" />
                                @error('issueDate') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-550 dark:text-slate-400 uppercase mb-2">Expiry Date (Optional)</label>
                                <input type="date" wire:model="expiryDate" class="w-full h-11 rounded-xl border border-slate-200 dark:border-slate-750 bg-slate-50 dark:bg-slate-900 px-4 text-sm text-slate-900 dark:text-white outline-none focus:ring-4 focus:ring-primary/10 transition-all" />
                                @error('expiryDate') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="px-6 py-4 bg-slate-50 dark:bg-slate-900/50 border-t border-slate-100 dark:border-slate-800 flex justify-end gap-3">
                        <button type="button" wire:click="closeUploadModal" class="px-4 py-2 text-xs font-bold bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-750 text-slate-750 dark:text-slate-300 rounded-lg transition-colors">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 text-xs font-bold bg-primary hover:bg-primary-dark text-white rounded-lg transition-colors" wire:loading.attr="disabled" wire:target="file">
                            Upload Document
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- History Modal -->
    @if($showHistoryModal && $selectedDocTypeForHistory)
        <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-xl shadow-xl overflow-hidden animate-in fade-in zoom-in-95 duration-200 flex flex-col max-h-[90vh]">
                <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center bg-slate-50 dark:bg-slate-900/50">
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Version History: {{ $selectedDocTypeForHistory->name }}</h3>
                    <button wire:click="closeHistoryModal" class="text-slate-400 hover:text-slate-500 dark:hover:text-slate-300">
                        <span class="material-symbols-outlined text-[20px]">close</span>
                    </button>
                </div>
                <div class="p-6 overflow-y-auto max-h-[60vh] space-y-4">
                    @forelse($historyDocumentsList as $version)
                        <div class="border border-slate-200 dark:border-slate-800 rounded-xl p-4 space-y-3 bg-slate-50/20 dark:bg-slate-800/10">
                            <div class="flex items-center justify-between">
                                <span class="px-2 py-0.5 text-[9px] font-bold rounded uppercase tracking-wider bg-slate-100 text-slate-650 dark:bg-slate-800 dark:text-slate-400">
                                    Version {{ $version->version_number }}
                                </span>
                                @php
                                    $versionStatusStyles = [
                                        'pending_review' => 'bg-blue-600 text-white dark:bg-blue-600 dark:text-white',
                                        'approved' => 'bg-emerald-600 text-white dark:bg-emerald-600 dark:text-white',
                                        'rejected' => 'bg-rose-600 text-white dark:bg-rose-600 dark:text-white',
                                    ];
                                    $vStyle = $versionStatusStyles[$version->status] ?? 'bg-slate-100 text-slate-700';
                                @endphp
                                <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wide {{ $vStyle }}">
                                    {{ ucwords(str_replace('_', ' ', $version->status)) }}
                                </span>
                            </div>
                            
                            <div class="text-xs space-y-1">
                                <p class="text-slate-450">Uploaded by: <strong class="font-bold text-slate-850 dark:text-slate-300">{{ $version->uploader?->name ?? 'N/A' }}</strong></p>
                                <p class="text-slate-400 text-[10px]">Uploaded at: {{ $version->created_at->format('Y-m-d H:i:s') }}</p>
                                @if($version->issue_date || $version->expiry_date)
                                    <p class="text-slate-400 text-[10px]">
                                        Validity: {{ $version->issue_date ? $version->issue_date->format('Y-m-d') : 'N/A' }} to {{ $version->expiry_date ? $version->expiry_date->format('Y-m-d') : 'Indefinite' }}
                                    </p>
                                @endif
                            </div>

                            @if($version->status === 'rejected')
                                <div class="p-2.5 rounded-lg bg-rose-50 dark:bg-rose-950/20 border border-rose-100/50 dark:border-rose-900/30 text-xs">
                                    <p class="font-bold text-rose-800 dark:text-rose-400">Rejection Reason: {{ ucwords(str_replace('_', ' ', $version->rejection_reason)) }}</p>
                                    @if($version->reviewer_notes)
                                        <p class="text-[11px] text-rose-700 dark:text-rose-450 mt-0.5">Note: "{{ $version->reviewer_notes }}"</p>
                                    @endif
                                </div>
                            @elseif($version->reviewer_notes)
                                <div class="p-2.5 rounded-lg bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800 text-xs">
                                    <p class="text-[11px] text-slate-700 dark:text-slate-400 italic">Reviewer Note: "{{ $version->reviewer_notes }}"</p>
                                </div>
                            @endif

                            <div class="flex justify-end pt-1">
                                <a href="{{ $version->getDownloadUrl() }}" target="_blank" class="px-2.5 py-1.5 bg-white hover:bg-slate-50 dark:bg-slate-900 dark:hover:bg-slate-850 text-slate-700 dark:text-slate-300 text-[10px] font-bold rounded-lg border border-slate-200 dark:border-slate-850 flex items-center gap-1">
                                    <span class="material-symbols-outlined text-xs">download</span>
                                    <span>Download File</span>
                                </a>
                            </div>
                        </div>
                    @empty
                        <p class="text-center text-xs text-slate-400 py-6">No uploads recorded.</p>
                    @endforelse
                </div>
                <div class="px-6 py-4 bg-slate-50 dark:bg-slate-900/50 border-t border-slate-100 dark:border-slate-800 flex justify-end">
                    <button type="button" wire:click="closeHistoryModal" class="px-4 py-2 text-xs font-bold bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-750 text-slate-750 dark:text-slate-350 rounded-lg transition-colors">
                        Close
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
