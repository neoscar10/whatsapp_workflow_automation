<div class="p-8 flex gap-8 h-[calc(100vh-80px)] overflow-hidden">
    <!-- Left Column: Metadata & Checklist -->
    <div class="w-4/12 flex flex-col gap-6 overflow-y-auto no-scrollbar pr-2 h-full">
        <!-- Back Button & Header -->
        <div class="flex items-center justify-between">
            <a href="{{ route('superadmin.verification-queue') }}" class="text-slate-500 hover:text-slate-700 dark:hover:text-slate-350 text-xs font-bold flex items-center gap-1">
                <span class="material-symbols-outlined text-[16px]">arrow_back</span>
                <span>Back to Queue</span>
            </a>

            <!-- Status Controls -->
            <div class="flex items-center gap-3">
                <a href="{{ route('superadmin.verification-review.download-all', ['id' => $verification->id]) }}" class="px-3 py-1.5 bg-primary hover:bg-primary-dark text-white text-[10px] font-bold rounded-lg uppercase tracking-wide flex items-center gap-1 shadow-sm">
                    <span class="material-symbols-outlined text-[14px]">download</span>
                    <span>Download All</span>
                </a>
                @if($verification->status === 'suspended')
                    <button type="button" wire:click="unsuspendVerification" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-[10px] font-bold rounded-lg uppercase tracking-wide">
                        Lift Suspension
                    </button>
                @else
                    <button type="button" wire:click="suspendVerification" class="px-3 py-1.5 bg-slate-900 hover:bg-black dark:bg-slate-800 dark:hover:bg-slate-750 text-white text-[10px] font-bold rounded-lg uppercase tracking-wide">
                        Suspend Verification
                    </button>
                @endif
            </div>
        </div>

        <!-- Summary Info Card -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm space-y-4">
            <div class="flex items-center gap-4">
                <div class="flex size-14 items-center justify-center rounded-xl bg-primary/10 text-primary font-bold uppercase text-lg">
                    {{ substr($verification->company->name, 0, 2) }}
                </div>
                <div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">{{ $verification->company->name }}</h3>
                    <p class="text-xs text-slate-450 dark:text-slate-500">Registered Country: <span class="uppercase font-bold">{{ $verification->company->country }}</span></p>
                </div>
            </div>

            <!-- Verification Progress Bar -->
            <div class="space-y-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                <div class="flex items-center justify-between text-xs">
                    <span class="font-bold text-slate-500">Current Status:
                        <span class="ml-1 uppercase text-primary font-bold">{{ str_replace('_', ' ', $verification->status) }}</span>
                    </span>
                    <span class="font-bold text-slate-900 dark:text-white">{{ $verification->progress_percentage }}% Approved</span>
                </div>
                <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-1.5">
                    <div class="bg-primary h-1.5 rounded-full" style="width: {{ $verification->progress_percentage }}%"></div>
                </div>
            </div>
        </div>

        <!-- Session Message inside workstation -->
        @if (session()->has('success_review'))
            <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                {{ session('success_review') }}
            </div>
        @endif

        <!-- Document Checklist Selection List -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm space-y-4">
            <h4 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">Required Checklist</h4>
            <div class="space-y-3">
                @foreach($verification->documents as $doc)
                    @php
                        $latest = $doc->latestVersion;
                        $statusBadge = 'bg-slate-100 text-slate-650 dark:bg-slate-800 dark:text-slate-400';
                        $statusText = 'Not Submitted';
                        
                        if ($doc->status === 'approved') {
                            $statusBadge = 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-350';
                            $statusText = 'Approved';
                        } elseif ($doc->status === 'rejected') {
                            $statusBadge = 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-350';
                            $statusText = 'Rejected';
                        } elseif ($doc->status === 'pending_review') {
                            $statusBadge = 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300';
                            $statusText = 'Pending Review';
                        }
                    @endphp
                    <div 
                        wire:click="selectDocument('{{ $doc->id }}')"
                        class="p-4 rounded-xl border cursor-pointer transition-all flex items-center justify-between gap-4 {{ $selectedDocId === $doc->id ? 'bg-primary/5 dark:bg-primary/10 border-primary ring-2 ring-primary/20' : 'bg-slate-50/20 dark:bg-slate-800/10 hover:bg-slate-50 dark:hover:bg-slate-850/50 border-slate-200 dark:border-slate-800' }}"
                    >
                        <div>
                            <span class="px-1.5 py-0.5 text-[8px] font-bold rounded uppercase tracking-wider {{ $doc->documentType->is_required ? 'bg-amber-100 text-amber-800 dark:bg-amber-950/25 dark:text-amber-400' : 'bg-slate-150 text-slate-700 dark:bg-slate-850 dark:text-slate-450' }}">
                                {{ $doc->documentType->is_required ? 'Required' : 'Optional' }}
                            </span>
                            <h5 class="font-bold text-slate-800 dark:text-white mt-1 text-xs">{{ $doc->documentType->name }}</h5>
                            @if($latest)
                                <p class="text-[10px] text-slate-400 font-mono mt-0.5">Uploaded: {{ $latest->created_at->format('Y-m-d') }}</p>
                            @endif
                        </div>
                        <span class="px-2 py-0.5 rounded text-[9px] font-bold tracking-wide uppercase shrink-0 {{ $statusBadge }}">
                            {{ $statusText }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Activity Log Timeline -->
        @if($verification->timeline->isNotEmpty())
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm space-y-4">
                <h4 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">Activity Timeline</h4>
                <div class="relative border-l border-slate-100 dark:border-slate-800 space-y-6" style="padding-left: 40px;">
                    @foreach($verification->timeline as $time)
                        <div class="relative">
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
                                    <p class="text-[10px] text-slate-400 mt-1">By: {{ $time->actor->name }}</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <!-- Right Column: Focus Document Workstation and Preview -->
    <div class="w-8/12 flex flex-col h-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
        @if($focusedDoc)
            @php
                $latest = $focusedDoc->latestVersion;
            @endphp
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/50 flex justify-between items-center">
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">{{ $focusedDoc->documentType->name }}</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">File Type: {{ $fileMime ?? 'Not Uploaded' }}</p>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto no-scrollbar p-6 space-y-6">
                @if($latest && $latest->status === 'pending_review')
                    <div class="flex justify-end">
                        <button type="button" wire:click="$set('showActionModal', true)" class="px-4 py-2 bg-primary hover:bg-primary-dark text-white text-xs font-bold rounded-lg transition-colors flex items-center gap-1.5 shadow-sm">
                            <span class="material-symbols-outlined text-sm">rate_review</span>
                            <span>Take Action</span>
                        </button>
                    </div>
                @endif
                <!-- Preview area -->
                <div class="border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden bg-slate-50 dark:bg-slate-950 flex items-center justify-center min-h-[300px]">
                    @if($previewUrl)
                        @if(Str::startsWith($fileMime, 'image/'))
                            <img src="{{ $previewUrl }}" class="max-w-full max-h-[400px] object-contain p-4" />
                        @elseif($fileMime === 'application/pdf')
                            <iframe src="{{ $previewUrl }}" class="w-full h-[400px] border-none"></iframe>
                        @else
                            <div class="p-8 text-center text-slate-550 dark:text-slate-400">
                                <span class="material-symbols-outlined text-[48px] mb-2 text-slate-400">description</span>
                                <p class="text-xs font-bold mb-3">{{ $latest->file_name }}</p>
                                <a href="{{ $previewUrl }}" target="_blank" class="px-4 py-2 bg-primary text-white text-xs font-bold rounded-lg transition-colors inline-flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-sm">open_in_new</span>
                                    <span>Download & Open File</span>
                                </a>
                            </div>
                        @endif
                    @else
                        <div class="p-8 text-center text-slate-400 dark:text-slate-500">
                            <span class="material-symbols-outlined text-[48px] mb-2 text-slate-350">cloud_off</span>
                            <p class="text-xs font-bold">No file has been uploaded for this document type yet.</p>
                        </div>
                    @endif
                </div>



                <!-- Expiry Metadata if exists -->
                @if($latest && ($latest->issue_date || $latest->expiry_date))
                    <div class="grid grid-cols-2 gap-4 border border-slate-100 dark:border-slate-800 p-4 rounded-2xl bg-slate-50/20 dark:bg-slate-800/10 text-xs text-slate-600 dark:text-slate-450">
                        <div>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Issue Date</span>
                            <span class="font-bold text-slate-900 dark:text-white">{{ $latest->issue_date ? $latest->issue_date->format('Y-m-d') : 'N/A' }}</span>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Expiry Date</span>
                            <span class="font-bold text-slate-900 dark:text-white">{{ $latest->expiry_date ? $latest->expiry_date->format('Y-m-d') : 'Indefinite' }}</span>
                        </div>
                    </div>
                @endif

                <!-- Version History list -->
                <div class="space-y-3">
                    <h4 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">File Version Log</h4>
                    <div class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($focusedDoc->versions as $v)
                            <div class="py-3 flex items-center justify-between gap-4 text-xs">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold text-slate-800 dark:text-white">v{{ $v->version_number }}</span>
                                        @php
                                            $vStyles = [
                                                'approved' => 'bg-emerald-100 text-emerald-850 dark:bg-emerald-950 dark:text-emerald-400',
                                                'rejected' => 'bg-rose-100 text-rose-850 dark:bg-rose-950 dark:text-rose-450',
                                                'pending_review' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
                                            ];
                                            $styleBadge = $vStyles[$v->status] ?? 'bg-slate-100 text-slate-700';
                                        @endphp
                                        <span class="px-1.5 py-0.5 rounded text-[8px] font-bold uppercase tracking-wider {{ $styleBadge }}">
                                            {{ str_replace('_', ' ', $v->status) }}
                                        </span>
                                    </div>
                                    <p class="text-[10px] text-slate-400 mt-0.5">Uploaded by {{ $v->uploader?->name ?? 'N/A' }} on {{ $v->created_at->format('Y-m-d H:i') }}</p>
                                </div>
                                <a href="{{ $v->getDownloadUrl() }}" target="_blank" class="px-2.5 py-1.5 bg-slate-50 hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-750 text-slate-750 dark:text-slate-350 text-[10px] font-bold rounded-lg border border-slate-200 dark:border-slate-850 flex items-center gap-1">
                                    <span class="material-symbols-outlined text-xs">download</span>
                                    <span>Download</span>
                                </a>
                            </div>
                        @empty
                            <p class="text-slate-400 text-xs py-4 text-center">No document versions uploaded yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        @else
            <div class="flex-1 flex flex-col items-center justify-center p-12 text-center text-slate-400 dark:text-slate-500">
                <span class="material-symbols-outlined text-[48px] text-slate-350 mb-2">find_in_page</span>
                <h4 class="font-bold text-slate-900 dark:text-white">No document selected</h4>
                <p class="text-xs max-w-xs mt-1 leading-relaxed">Select a verification document from the checklist on the left to begin reviewing files.</p>
            </div>
        @endif
    </div>

    <!-- Rejection Dialog Modal -->
    @if($showRejectionDialog)
        <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-md shadow-xl overflow-hidden animate-in fade-in zoom-in-95 duration-200">
                <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center bg-slate-50 dark:bg-slate-900/50">
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Reject Document</h3>
                    <button wire:click="closeRejectionDialog" class="text-slate-400 hover:text-slate-500 dark:hover:text-slate-300">
                        <span class="material-symbols-outlined text-[20px]">close</span>
                    </button>
                </div>
                <form wire:submit.prevent="reject">
                    <div class="p-6 space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-550 dark:text-slate-400 uppercase mb-2">Rejection Reason Category</label>
                            <select wire:model="rejectionReason" class="w-full h-11 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 px-4 text-xs font-semibold outline-none text-slate-900 dark:text-white focus:ring-4 focus:ring-primary/10 transition-all">
                                <option value="document_unclear">Document Unclear</option>
                                <option value="expired_document">Expired Document</option>
                                <option value="wrong_document">Wrong Document</option>
                                <option value="name_mismatch">Name Mismatch</option>
                                <option value="address_mismatch">Address Mismatch</option>
                                <option value="incomplete_document">Incomplete Document</option>
                                <option value="fraud_concern">Fraud Concern</option>
                                <option value="other">Other / Custom</option>
                            </select>
                            @error('rejectionReason') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-550 dark:text-slate-400 uppercase mb-2">Reviewer Explanation Note</label>
                            <textarea wire:model="reviewerNotes" rows="4" class="w-full p-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-xs font-medium text-slate-900 dark:text-white outline-none focus:ring-4 focus:ring-primary/10 transition-all resize-none" placeholder="Provide instructions on what the user needs to correct..."></textarea>
                            @error('reviewerNotes') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="px-6 py-4 bg-slate-50 dark:bg-slate-900/50 border-t border-slate-100 dark:border-slate-800 flex justify-end gap-3">
                        <button type="button" wire:click="closeRejectionDialog" class="px-4 py-2 text-xs font-bold bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-750 text-slate-750 dark:text-slate-350 rounded-lg transition-colors">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 text-xs font-bold bg-rose-650 hover:bg-rose-700 text-white rounded-lg transition-colors">
                            Confirm Rejection
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Action Modal -->
    @if($showActionModal && $latest)
        <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-md shadow-xl overflow-hidden animate-in fade-in zoom-in-95 duration-200">
                <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center bg-slate-50 dark:bg-slate-900/50">
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Review {{ $focusedDoc->documentType->name }}</h3>
                    <button wire:click="$set('showActionModal', false)" class="text-slate-400 hover:text-slate-500 dark:hover:text-slate-300">
                        <span class="material-symbols-outlined text-[20px]">close</span>
                    </button>
                </div>
                <div class="p-6 space-y-4">
                    <div class="space-y-2">
                        <label class="block text-xs font-bold text-slate-550 dark:text-slate-400 uppercase">Reviewer Notes (Optional)</label>
                        <textarea wire:model="reviewerNotes" rows="3" class="w-full p-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-xs font-medium text-slate-900 dark:text-white outline-none focus:ring-4 focus:ring-primary/10 transition-all resize-none" placeholder="Provide notes, validations, or internal memos..."></textarea>
                    </div>
                </div>
                <div class="px-6 py-4 bg-slate-50 dark:bg-slate-900/50 border-t border-slate-100 dark:border-slate-800 flex justify-end gap-3">
                    <button type="button" wire:click="openRejectionDialog('{{ $latest->id }}')" class="px-4 py-2 bg-rose-650 hover:bg-rose-700 text-white text-xs font-bold rounded-lg transition-colors flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm">close</span>
                        <span>Reject</span>
                    </button>
                    <button type="button" wire:click="approve('{{ $latest->id }}')" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg transition-colors flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm">check</span>
                        <span>Approve</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
