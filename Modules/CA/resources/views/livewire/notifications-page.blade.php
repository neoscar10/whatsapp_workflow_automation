<div class="p-6 lg:p-8 w-full max-w-7xl mx-auto font-['Inter']">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
        <div>
            <h1 class="text-[24px] font-semibold tracking-tight text-slate-900 dark:text-white">Document Processing Logs</h1>
            <p class="text-[14px] text-[#424656] dark:text-slate-400 mt-1">Audit and align inbound WhatsApp files with compliance tasks.</p>
        </div>
        <!-- Status Filters -->
        <div class="flex items-center gap-2 bg-[#f6f3f2] dark:bg-slate-800 p-1.5 rounded-xl border border-slate-200 dark:border-slate-700">
            <button type="button" wire:click="$set('filterStatus', 'pending')" 
                class="px-4 py-1.5 rounded-lg text-xs font-semibold cursor-pointer transition-all {{ $filterStatus === 'pending' ? 'bg-white dark:bg-slate-700 text-blue-600 dark:text-white shadow-sm' : 'text-slate-500 hover:text-slate-800' }}">
                Pending Review
            </button>
            <button type="button" wire:click="$set('filterStatus', 'resolved')" 
                class="px-4 py-1.5 rounded-lg text-xs font-semibold cursor-pointer transition-all {{ $filterStatus === 'resolved' ? 'bg-white dark:bg-slate-700 text-blue-600 dark:text-white shadow-sm' : 'text-slate-500 hover:text-slate-800' }}">
                Processed
            </button>
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
            <span class="material-symbols-outlined text-[18px]">error</span>
            {{ session('error') }}
        </div>
    @endif

    <!-- Notifications List -->
    @if($notifications->isEmpty())
        <div class="text-center py-16 bg-white dark:bg-slate-800 rounded-2xl border border-dashed border-slate-300 dark:border-slate-700">
            <span class="material-symbols-outlined text-[48px] text-slate-400 mb-2">notifications_off</span>
            <h3 class="font-bold text-slate-900 dark:text-white text-base">All Clean!</h3>
            <p class="text-sm text-[#424656] dark:text-slate-400 mt-1">No document events require attention right now.</p>
        </div>
    @else
        <div class="space-y-4">
            @foreach($notifications as $notif)
                @php
                    $isMatched = $notif->type === 'document_matched';
                    $ai = $notif->metadata_json['ai_classification'] ?? [];
                    $outcome = $notif->metadata_json['matching_outcome'] ?? 'unsupported';
                    $docId = $notif->metadata_json['ca_document_id'] ?? null;
                    $doc = \Modules\CA\Models\CADocument::find($docId);
                @endphp
                <div class="bg-white dark:bg-slate-800 border border-[#c2c6d8]/40 dark:border-slate-700 rounded-2xl p-6 shadow-sm flex flex-col lg:flex-row lg:items-center justify-between gap-6 hover:shadow-md transition-all">
                    
                    <!-- Event Metadata -->
                    <div class="flex-1 space-y-3">
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase border {{ $isMatched ? 'bg-green-50 text-green-700 border-green-200 dark:bg-green-900/20 dark:text-green-400' : 'bg-red-50 text-red-700 border-red-200 dark:bg-red-900/20 dark:text-red-400' }}">
                                {{ $isMatched ? 'Auto-Matched' : 'Unmatched' }}
                            </span>
                            <span class="text-xs text-slate-400">{{ $notif->created_at->diffForHumans() }}</span>
                        </div>

                        <div>
                            <h3 class="font-bold text-slate-900 dark:text-white text-lg">{{ $notif->title }}</h3>
                            <p class="text-sm text-slate-600 dark:text-slate-300 mt-1">{{ $notif->message }}</p>
                        </div>

                        <!-- Info Grid -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2 text-xs text-[#727687] dark:text-slate-400 bg-slate-50 dark:bg-slate-900/40 p-4 rounded-xl">
                            <div>
                                <span class="block font-bold text-slate-400 uppercase text-[9px] tracking-wider">Client</span>
                                <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $notif->client?->client_name ?? 'N/A' }}</span>
                            </div>
                            <div>
                                <span class="block font-bold text-slate-400 uppercase text-[9px] tracking-wider">Detected Type</span>
                                <span class="font-semibold text-slate-700 dark:text-slate-200 capitalize">{{ str_replace('_', ' ', $ai['detected_document_type'] ?? 'unknown') }}</span>
                            </div>
                            <div>
                                <span class="block font-bold text-slate-400 uppercase text-[9px] tracking-wider">AI Confidence</span>
                                <span class="font-semibold text-slate-700 dark:text-slate-200">{{ number_format(($ai['confidence'] ?? 0) * 100, 1) }}%</span>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    @if($notif->status === 'pending')
                        <div class="flex sm:flex-row flex-col items-center gap-2 lg:border-l lg:border-slate-100 dark:lg:border-slate-700 lg:pl-6">
                            @if($doc)
                                <a href="{{ route('ca.documents.download', $doc->id) }}" target="_blank"
                                    class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-4 py-2 border border-slate-200 dark:border-slate-600 hover:bg-slate-50 text-slate-700 dark:text-slate-200 rounded-xl text-xs font-semibold shadow-sm transition-all">
                                    <span class="material-symbols-outlined text-[16px]">download</span>
                                    Download
                                </a>
                            @endif

                            @if($isMatched)
                                <button type="button" wire:click="approveDocument({{ $notif->id }})"
                                    class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-5 py-2 cursor-pointer bg-green-600 hover:bg-green-700 text-white rounded-xl text-xs font-bold shadow-sm transition-all active:scale-95">
                                    <span class="material-symbols-outlined text-[16px]">check</span>
                                    Approve Match
                                </button>
                            @else
                                <button type="button" wire:click="openReassignModal({{ $notif->id }})"
                                    class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-5 py-2 cursor-pointer bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-sm transition-all active:scale-95">
                                    <span class="material-symbols-outlined text-[16px]">assignment</span>
                                    Assign manually
                                </button>
                            @endif

                            <button type="button" wire:click="rejectDocument({{ $notif->id }})"
                                class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-4 py-2 border border-red-200 text-red-600 hover:bg-red-50 dark:border-red-900/30 dark:hover:bg-red-900/10 rounded-xl text-xs font-semibold transition-all">
                                <span class="material-symbols-outlined text-[16px]">close</span>
                                Reject
                            </button>
                        </div>
                    @else
                        <div class="flex items-center gap-2 lg:border-l lg:border-slate-100 dark:lg:border-slate-700 lg:pl-6 text-slate-400 text-xs font-semibold">
                            <span class="material-symbols-outlined text-[18px]">verified</span>
                            Processed
                        </div>
                    @endif

                </div>
            @endforeach

            <div class="mt-6">
                {{ $notifications->links() }}
            </div>
        </div>
    @endif

    <!-- Reassign/Manual Assignment Modal -->
    @if($showReassignModal)
        <div class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm animate-fade-in">
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden border border-[#c2c6d8]/50 dark:border-slate-700 flex flex-col">
                <!-- Modal Header -->
                <div class="px-6 py-4 border-b border-[#c2c6d8]/50 dark:border-slate-700 flex items-center justify-between bg-slate-50 dark:bg-slate-800/50">
                    <h3 class="text-lg font-bold text-[#1c1b1b] dark:text-white flex items-center gap-2">
                        <span class="material-symbols-outlined text-blue-600 dark:text-blue-400">assignment</span>
                        Assign Inbound Document
                    </h3>
                    <button type="button" wire:click="$set('showReassignModal', false)" class="text-slate-400 hover:text-slate-600 transition-colors">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6 space-y-4">
                    <p class="text-sm text-[#424656] dark:text-slate-400">Select which pending compliance requirement this document matches:</p>
                    
                    <select wire:model="targetRequirementId" class="w-full px-4 py-2.5 bg-[#f6f3f2] dark:bg-slate-900 border border-[#c2c6d8]/50 dark:border-slate-700 rounded-xl text-sm focus:border-blue-600 text-slate-900 dark:text-white transition-all">
                        <option value="">-- Choose Requirement --</option>
                        @foreach($pendingRequirements as $req)
                            <option value="{{ $req->id }}">{{ $req->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Modal Footer -->
                <div class="px-6 py-4 border-t border-[#c2c6d8]/50 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex justify-end gap-3">
                    <button type="button" wire:click="$set('showReassignModal', false)" class="px-6 py-2.5 cursor-pointer rounded-xl border border-[#c2c6d8]/50 dark:border-slate-700 hover:bg-white dark:hover:bg-slate-700 transition-colors font-semibold text-[#424656] dark:text-slate-300">
                        Cancel
                    </button>
                    <button type="button" wire:click="reassignDocument" class="px-8 py-2.5 cursor-pointer bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 transition-colors shadow-sm flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px]">publish</span>
                        Assign Document
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
