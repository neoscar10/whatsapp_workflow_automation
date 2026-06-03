<div class="p-8 space-y-6 flex-1 overflow-y-auto">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">Verification Management</h2>
            <p class="text-sm text-slate-505 dark:text-slate-400 mt-1">Review pending corporate document uploads and verify company accounts.</p>
        </div>
    </div>

    <!-- Analytics Dashboard Widgets -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-850 p-6 rounded-2xl shadow-sm flex items-center gap-4">
            <div class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-slate-50 text-slate-600 dark:bg-slate-800 dark:text-slate-400">
                <span class="material-symbols-outlined text-[24px]">corporate_fare</span>
            </div>
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Total Companies</span>
                <span class="text-2xl font-bold text-slate-900 dark:text-white">{{ $totalCompanies }}</span>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-850 p-6 rounded-2xl shadow-sm flex items-center gap-4">
            <div class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-950/20 dark:text-emerald-400">
                <span class="material-symbols-outlined text-[24px]">verified</span>
            </div>
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Verified Companies</span>
                <span class="text-2xl font-bold text-slate-900 dark:text-white">{{ $verifiedCompanies }}</span>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-850 p-6 rounded-2xl shadow-sm flex items-center gap-4">
            <div class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-950/20 dark:text-blue-400">
                <span class="material-symbols-outlined text-[24px]">pending_actions</span>
            </div>
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Pending Reviews</span>
                <span class="text-2xl font-bold text-slate-900 dark:text-white">{{ $pendingReviews }}</span>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-850 p-6 rounded-2xl shadow-sm flex items-center gap-4">
            <div class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-rose-50 text-rose-600 dark:bg-rose-950/20 dark:text-rose-450">
                <span class="material-symbols-outlined text-[24px]">error</span>
            </div>
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Rejected Documents</span>
                <span class="text-2xl font-bold text-slate-900 dark:text-white">{{ $rejectedDocsCount }}</span>
            </div>
        </div>
    </div>

    <!-- Filters Panel -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm flex flex-col md:flex-row gap-4 items-center justify-between">
        <div class="relative w-full flex-1 max-w-md">
            <span class="material-symbols-outlined absolute left-3.5 top-3 text-slate-400 text-lg">search</span>
            <input 
                type="text" 
                wire:model.live="search"
                placeholder="Search by company name..." 
                class="w-full h-11 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 pl-10 pr-4 text-xs font-semibold outline-none focus:ring-4 focus:ring-primary/10 transition-all text-slate-900 dark:text-white"
            />
        </div>

        <div class="flex gap-4 w-full md:w-auto shrink-0">
            <select wire:model.live="statusFilter" class="h-11 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 px-4 text-xs font-semibold outline-none text-slate-750 dark:text-slate-300">
                <option value="">All Statuses</option>
                <option value="not_started">Not Started</option>
                <option value="in_progress">In Progress</option>
                <option value="under_review">Under Review</option>
                <option value="partially_approved">Partially Approved</option>
                <option value="verified">Verified</option>
                <option value="rejected">Rejected</option>
                <option value="expired">Expired</option>
                <option value="suspended">Suspended</option>
            </select>

            <select wire:model.live="countryFilter" class="h-11 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 px-4 text-xs font-semibold outline-none text-slate-750 dark:text-slate-300">
                <option value="">All Countries</option>
                @foreach($countries as $code)
                    <option value="{{ $code }}">{{ strtoupper($code) }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Table Directory -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-700 text-[10px] font-bold uppercase tracking-wider text-slate-555 dark:text-slate-400">
                        <th class="p-4">Company Details</th>
                        <th class="p-4">Country</th>
                        <th class="p-4">Verification Status</th>
                        <th class="p-4">Submission Progress</th>
                        <th class="p-4">Pending Docs</th>
                        <th class="p-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-xs font-medium">
                    @forelse($verifications as $ver)
                        @php
                            $statusColors = [
                                'not_started' => 'text-slate-500 dark:text-slate-400',
                                'in_progress' => 'text-amber-600 dark:text-amber-400',
                                'under_review' => 'text-blue-600 dark:text-blue-400',
                                'partially_approved' => 'text-indigo-600 dark:text-indigo-400',
                                'verified' => 'text-emerald-600 dark:text-emerald-400',
                                'rejected' => 'text-rose-600 dark:text-rose-400',
                                'expired' => 'text-red-600 dark:text-red-400',
                                'suspended' => 'text-slate-900 dark:text-slate-200 font-extrabold',
                            ];
                            $textColor = $statusColors[$ver->status] ?? 'text-slate-500';
                            
                            // Count pending docs
                            $pendingDocsCount = 0;
                            foreach ($ver->documents as $doc) {
                                if ($doc->latestVersion && $doc->latestVersion->status === 'pending_review') {
                                    $pendingDocsCount++;
                                }
                            }
                        @endphp
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
                            <td class="p-4">
                                <p class="font-bold text-slate-900 dark:text-white text-sm">{{ $ver->company->name }}</p>
                                <p class="text-[10px] text-slate-450 dark:text-slate-500 font-mono mt-0.5">{{ $ver->company->primary_email }}</p>
                            </td>
                            <td class="p-4 uppercase text-slate-700 dark:text-slate-300">
                                {{ $ver->company->country }}
                            </td>
                            <td class="p-4">
                                <span class="text-[10px] font-extrabold uppercase tracking-wide {{ $textColor }}">
                                    {{ str_replace('_', ' ', $ver->status) }}
                                </span>
                            </td>
                            <td class="p-4 w-48">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-24 bg-slate-100 dark:bg-slate-800 rounded-full h-1.5 shrink-0">
                                        <div class="bg-primary h-1.5 rounded-full" style="width: {{ $ver->progress_percentage }}%"></div>
                                    </div>
                                    <span class="font-bold text-slate-700 dark:text-slate-350">{{ $ver->progress_percentage }}%</span>
                                </div>
                            </td>
                            <td class="p-4 text-slate-700 dark:text-slate-300 font-bold">
                                @if($pendingDocsCount > 0)
                                    <span class="text-blue-600 dark:text-blue-400 flex items-center gap-1">
                                        <span class="material-symbols-outlined text-sm font-bold">mark_as_unread</span>
                                        <span>{{ $pendingDocsCount }} doc(s)</span>
                                    </span>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="p-4 text-right">
                                <a href="{{ route('superadmin.verification-review', ['id' => $ver->id]) }}" class="px-3.5 py-1.5 bg-primary hover:bg-primary-dark text-white text-xs font-bold rounded-lg transition-colors flex items-center gap-1 inline-flex">
                                    <span class="material-symbols-outlined text-sm">rate_review</span>
                                    <span>Review Workspace</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-slate-400 dark:text-slate-500 font-bold">
                                No companies found matching the search criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($verifications->hasPages())
            <div class="p-4 border-t border-slate-100 dark:border-slate-850">
                {{ $verifications->links() }}
            </div>
        @endif
    </div>
</div>
