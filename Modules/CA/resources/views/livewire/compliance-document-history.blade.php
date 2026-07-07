<div class="p-6 lg:p-8 w-full max-w-7xl mx-auto font-['Inter']">
    <!-- Breadcrumbs & Navigation -->
    <div class="flex items-center gap-2 text-[14px] text-[#424656] dark:text-slate-400 mb-4">
        <a href="{{ route('ca.clients.show', $client->id) }}" class="hover:text-primary dark:hover:text-blue-400 transition-colors">
            {{ $client->client_name }}
        </a>
        <span class="material-symbols-outlined text-[16px]">chevron_right</span>
        <a href="{{ route('ca.clients.compliance.workspace', [$client->id, $clientCompliance->id]) }}" class="hover:text-primary dark:hover:text-blue-400 transition-colors">
            {{ $clientCompliance->compliance->name }}
        </a>
        <span class="material-symbols-outlined text-[16px]">chevron_right</span>
        <span class="font-medium text-[#1c1b1b] dark:text-slate-300">Document History Folder</span>
    </div>

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
        <div>
            <h1 class="text-[24px] font-semibold tracking-tight text-slate-900 dark:text-white flex items-center gap-2">
                <span class="material-symbols-outlined text-amber-500 text-[28px]">folder</span>
                Collected Documents
            </h1>
            <p class="text-[14px] text-[#424656] dark:text-slate-400 mt-1">Audit log of all documents received for {{ $clientCompliance->compliance->name }}.</p>
        </div>
        <a href="{{ route('ca.clients.compliance.workspace', [$client->id, $clientCompliance->id]) }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-[#424656] dark:text-slate-200 rounded-xl font-semibold text-[13px] transition-all shadow-sm">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span>
            Back to Workspace
        </a>
    </div>

    <!-- Filters & Search -->
    <div class="bg-white dark:bg-slate-800 border border-[#c2c6d8] dark:border-slate-700 rounded-2xl p-6 shadow-sm mb-8">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
            <!-- Search -->
            <div class="md:col-span-6 relative">
                <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-[#727687] dark:text-slate-400">search</span>
                <input type="text" wire:model.live.debounce.300ms="search" class="w-full pl-12 pr-4 py-3 bg-[#f6f3f2] dark:bg-slate-900 border-none rounded-xl focus:ring-2 focus:ring-primary/20 text-[14px] text-[#1c1b1b] dark:text-white transition-all placeholder:text-[#727687] dark:placeholder:text-slate-500" placeholder="Search files by name...">
            </div>
            
            <!-- Requirement filter -->
            <div class="md:col-span-3">
                <select wire:model.live="requirementFilter" class="w-full px-4 py-3 bg-[#f6f3f2] dark:bg-slate-900 border-none rounded-xl focus:ring-2 focus:ring-primary/20 text-[14px] text-[#1c1b1b] dark:text-white transition-all appearance-none cursor-pointer">
                    <option value="">All Requirements</option>
                    @foreach($requirements as $req)
                        <option value="{{ $req->id }}">{{ $req->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Status filter -->
            <div class="md:col-span-3">
                <select wire:model.live="statusFilter" class="w-full px-4 py-3 bg-[#f6f3f2] dark:bg-slate-900 border-none rounded-xl focus:ring-2 focus:ring-primary/20 text-[14px] text-[#1c1b1b] dark:text-white transition-all appearance-none cursor-pointer">
                    <option value="">All Statuses</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                    <option value="uploaded">Uploaded / In Review</option>
                </select>
            </div>
        </div>
    </div>

    <!-- History Table -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col overflow-hidden">
        <div class="overflow-x-auto no-scrollbar">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="p-4 text-[11px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">File Name</th>
                        <th class="p-4 text-[11px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">Requirement</th>
                        <th class="p-4 text-[11px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">Submitted Date</th>
                        <th class="p-4 text-[11px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">Uploader</th>
                        <th class="p-4 text-[11px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">Status</th>
                        <th class="p-4 text-[11px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($documents as $doc)
                        @php
                            $statusColor = match($doc->status) {
                                'approved' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400',
                                'rejected' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
                                'uploaded', 'under_review' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
                                default => 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300',
                            };
                            $statusIcon = match($doc->status) {
                                'approved' => 'verified',
                                'rejected' => 'cancel',
                                'uploaded', 'under_review' => 'hourglass_top',
                                default => 'description',
                            };
                            $extIcon = in_array($doc->extension, ['pdf']) ? 'picture_as_pdf' : (in_array($doc->extension, ['jpg','jpeg','png','gif']) ? 'image' : 'description');
                        @endphp
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                            <td class="p-4 min-w-[200px]">
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-slate-900 dark:text-white truncate" title="{{ $doc->document_name ?: $doc->original_filename }}">
                                        {{ $doc->document_name ?: $doc->original_filename }}
                                    </p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ number_format($doc->file_size / 1024, 1) }} KB</p>
                                </div>
                            </td>
                            <td class="p-4 text-sm font-medium text-slate-700 dark:text-slate-300">
                                {{ $doc->clientComplianceRequirement->name ?? 'Unlinked' }}
                            </td>
                            <td class="p-4 text-sm text-slate-500 dark:text-slate-400">
                                {{ $doc->created_at->format('d M Y, h:i A') }}
                            </td>
                            <td class="p-4 text-sm text-slate-600 dark:text-slate-400">
                                {{ $doc->uploadedBy->name ?? 'Client' }}
                            </td>
                            <td class="p-4">
                                <div class="flex flex-col gap-1 w-fit">
                                    <span class="px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider inline-flex items-center gap-1 {{ $statusColor }}">
                                        <span class="material-symbols-outlined text-[12px]">{{ $statusIcon }}</span>
                                        {{ str_replace('_', ' ', $doc->status) }}
                                    </span>
                                    @if($doc->status === 'rejected' && !empty($doc->metadata_json['rejection_reason']))
                                        <span class="text-[11px] text-red-600 dark:text-red-400 italic max-w-[250px] truncate" title="Reason: {{ $doc->metadata_json['rejection_reason'] }}">
                                            Reason: {{ $doc->metadata_json['rejection_reason'] }}
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="p-4 text-right">
                                <a href="{{ route('ca.documents.download', $doc->id) }}" target="_blank" class="inline-flex items-center justify-center w-8 h-8 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 text-[#727687] dark:text-slate-400 hover:text-primary transition-colors cursor-pointer" title="Download">
                                    <span class="material-symbols-outlined text-[18px]">download</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-12 text-center">
                                <div class="flex flex-col items-center gap-4">
                                    <div class="size-16 rounded-2xl bg-slate-50 dark:bg-slate-800/50 flex items-center justify-center">
                                        <span class="material-symbols-outlined text-slate-300 dark:text-slate-600 text-[32px]">folder_off</span>
                                    </div>
                                    <div class="max-w-xs">
                                        <p class="text-sm font-bold text-slate-900 dark:text-white">No documents found</p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 mb-4">Adjust your filters to see older submissions.</p>
                                        <button wire:click="$set('search', '')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-lg font-medium text-[13px] transition-all">
                                            Clear Filters
                                        </button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($documents->hasPages())
            <div class="p-4 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-200 dark:border-slate-800">
                {{ $documents->links() }}
            </div>
        @endif
    </div>
</div>
