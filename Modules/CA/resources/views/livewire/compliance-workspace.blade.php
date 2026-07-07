<div class="p-6 lg:p-8 w-full max-w-7xl mx-auto font-['Inter']">
    <!-- Flash Messages -->
    @if(session('message'))
        <div class="mb-6 px-5 py-3 bg-teal-50 dark:bg-teal-900/20 border border-teal-200 dark:border-teal-800/30 rounded-xl text-teal-800 dark:text-teal-300 text-sm font-medium flex items-center gap-2">
            <span class="material-symbols-outlined text-[18px]">check_circle</span>
            {{ session('message') }}
        </div>
    @endif

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
        <div>
            <div class="flex items-center gap-2 text-[14px] text-[#424656] dark:text-slate-400 mb-2">
                <a href="{{ route('ca.clients.show', $clientCompliance->client->id) }}" class="hover:text-primary dark:hover:text-blue-400 transition-colors">
                    {{ $clientCompliance->client->client_name }}
                </a>
                <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                <span class="font-medium text-[#1c1b1b] dark:text-slate-300">Workspace</span>
            </div>
            <h1 class="text-[24px] font-semibold tracking-tight text-primary dark:text-blue-400">
                {{ $clientCompliance->compliance->name }}
            </h1>
        </div>
        <div class="flex gap-3">
            @if($clientCompliance->health_status === 'overdue')
                <span class="px-4 py-2 bg-[#ffdad6] text-[#93000a] dark:bg-red-900/30 dark:text-red-400 rounded-xl font-medium text-[14px] flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">warning</span> Action Overdue
                </span>
            @endif
            <button class="inline-flex items-center gap-2 px-6 py-2.5 bg-[#f6f3f2] hover:bg-[#e5e2e1] dark:bg-slate-800 dark:hover:bg-slate-700 text-[#1c1b1b] dark:text-white rounded-xl font-medium text-[14px] transition-all shadow-sm">
                <span class="material-symbols-outlined text-[20px]">notifications</span>
                Remind Client
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <!-- Main Document Area -->
        <div class="lg:col-span-8 space-y-6">

            <!-- Documents Grid -->
            <div class="bg-white dark:bg-slate-800 border border-[#c2c6d8] dark:border-slate-700 rounded-2xl p-6 lg:p-8 shadow-sm">
                <div class="flex items-center justify-between mb-6 pb-4 border-b border-[#c2c6d8]/30 dark:border-slate-700/50">
                    <h2 class="text-[18px] font-semibold text-[#1c1b1b] dark:text-white flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary dark:text-blue-400">folder_open</span>
                        Document Requirements
                    </h2>
                    <a href="{{ route('ca.clients.compliance.history', [$client->id, $clientCompliance->id]) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-amber-50 hover:bg-amber-100 dark:bg-amber-900/20 dark:hover:bg-amber-900/30 text-amber-800 dark:text-amber-300 rounded-xl font-bold text-[13px] transition-all border border-amber-200/50 dark:border-amber-900/50 shadow-sm cursor-pointer">
                        <span class="material-symbols-outlined text-[18px]">folder</span>
                        View History Folder
                    </a>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @forelse($clientCompliance->clientRequirements->where('is_recurring', true) as $req)
                        @php
                            $hasAutomation = $req->automationDocuments->contains(function ($docLink) {
                                return $docLink->clientAutomation && $docLink->clientAutomation->is_enabled && $docLink->clientAutomation->status === 'active';
                            });
                            $isAwaitingReview = in_array($req->status, ['uploaded', 'under_review']);
                        @endphp
                        <div class="group bg-white dark:bg-slate-800 rounded-xl border {{ $req->status === 'approved' ? 'border-[#006a61]/30 dark:border-teal-500/30' : 'border-[#c2c6d8]/50 dark:border-slate-700' }} p-5 transition-all cursor-pointer relative overflow-hidden shadow-sm hover:shadow-md hover:border-primary/30 flex flex-col justify-between h-full">
                            
                            <div>
                                <!-- Header Row: Status pill & Settings button -->
                                <div class="flex items-center justify-between mb-3 border-b border-[#c2c6d8]/30 dark:border-slate-700/50 pb-3">
                                    <span class="px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider inline-block
                                        {{ $req->status === 'approved' ? 'bg-[#86f2e4]/30 text-[#006f66] dark:text-teal-400' : 
                                           ($req->status === 'uploaded' || $req->status === 'under_review' ? 'bg-[#dae1ff] text-[#001849] dark:bg-blue-900/30 dark:text-blue-400' : 
                                           ($req->status === 'rejected' ? 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400' :
                                           'bg-[#f6f3f2] text-[#424656] dark:bg-slate-700 dark:text-slate-300')) }}">
                                        {{ str_replace('_', ' ', $req->status) }}
                                    </span>
                                    <div class="flex items-center gap-2 shrink-0">
                                        <button type="button" wire:click="openRecurrenceModal({{ $req->id }})" class="w-8 h-8 rounded-full hover:bg-slate-100 dark:hover:bg-slate-700 flex items-center justify-center text-[#727687] dark:text-slate-400 transition-colors cursor-pointer" title="Configure Schedule">
                                            <span class="material-symbols-outlined text-[18px]">settings</span>
                                        </button>
                                        @if($req->status === 'approved')
                                            <div class="w-8 h-8 rounded-full bg-[#006a61]/10 flex items-center justify-center text-[#006a61] dark:text-teal-400">
                                                <span class="material-symbols-outlined text-[18px]">verified</span>
                                            </div>
                                        @elseif($isAwaitingReview)
                                            <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center text-primary dark:text-blue-400">
                                                <span class="material-symbols-outlined text-[18px]">hourglass_top</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Full Width Blocks -->
                                @if($hasAutomation)
                                    <div class="w-full flex items-center gap-2 p-2.5 rounded-xl bg-green-50 text-green-700 dark:bg-green-950/20 dark:text-green-400 border border-green-200/40 dark:border-green-900/30 mb-2">
                                        <span class="material-symbols-outlined text-[18px]">smart_toy</span>
                                        <span class="text-[11px] font-bold uppercase tracking-wider">Auto-Reminders Active</span>
                                    </div>
                                @endif

                                @if($req->next_due_date)
                                    @php
                                        $parsedDate = \Carbon\Carbon::parse($req->next_due_date);
                                        $cfg = $req->recurrence_config ?? [];
                                        $schedules = $cfg['schedules'] ?? [];
                                        $summaryParts = [];
                                        foreach ($schedules as $sched) {
                                            $freq = $sched['frequency'] ?? '';
                                            $sConf = $sched['config'] ?? [];
                                            if ($freq === 'daily') {
                                                $summaryParts[] = 'Daily at ' . ($sConf['time'] ?? '09:00');
                                            } elseif ($freq === 'weekly') {
                                                $summaryParts[] = 'Weekly: ' . implode(', ', $sConf['days'] ?? []);
                                            } elseif ($freq === 'monthly') {
                                                $summaryParts[] = 'Monthly: Day ' . ($sConf['day_of_month'] ?? '1');
                                            } elseif ($freq === 'quarterly') {
                                                $summaryParts[] = 'Quarterly (' . ($sConf['quarter_type'] ?? 'calendar') . ')';
                                            } elseif ($freq === 'yearly') {
                                                $summaryParts[] = 'Yearly: ' . ($sConf['day'] ?? '1') . '/' . ($sConf['month'] ?? '1');
                                            }
                                        }
                                        $summary = implode(' & ', $summaryParts);
                                        if (empty($summary)) {
                                            $summary = ucfirst($req->recurrence_frequency ?? 'Monthly');
                                        }
                                        $isOverdue = $parsedDate->isPast() && $req->status !== 'approved';
                                    @endphp
                                    <div class="w-full flex items-center gap-2 p-2.5 rounded-xl {{ $isOverdue ? 'bg-red-50 text-red-700 dark:bg-red-950/20 dark:text-red-400 border border-red-200/40 dark:border-red-900/30' : 'bg-blue-50 text-blue-700 dark:bg-blue-950/20 dark:text-blue-400 border border-blue-200/40 dark:border-blue-900/30' }} mb-3" title="{{ $summary }}">
                                        <span class="material-symbols-outlined text-[18px]">calendar_today</span>
                                        <div class="flex flex-col min-w-0">
                                            <span class="text-[11px] font-bold uppercase tracking-wider truncate leading-tight">{{ $summary }}</span>
                                            <span class="text-[10px] font-semibold text-blue-600/80 dark:text-blue-400/80 mt-0.5 truncate">Next: {{ $parsedDate->format('M d, Y') }}</span>
                                        </div>
                                    </div>
                                @else
                                    <div class="w-full flex items-center gap-2 p-2.5 rounded-xl bg-amber-50 text-amber-700 dark:bg-amber-950/20 dark:text-amber-400 border border-amber-200/40 dark:border-amber-900/30 mb-3">
                                        <span class="material-symbols-outlined text-[18px]">warning</span>
                                        <span class="text-[11px] font-bold uppercase tracking-wider">Schedule Not Configured</span>
                                    </div>
                                @endif

                                <!-- Full Width Name & Description -->
                                <div class="w-full mb-3">
                                    <h4 class="font-bold text-[#1c1b1b] dark:text-white text-[15px] leading-tight w-full">{{ $req->name ?? $req->complianceRequirement?->name ?? 'Requirement' }}</h4>
                                    @if($req->complianceRequirement && $req->complianceRequirement->description)
                                        <p class="text-[12px] text-[#727687] dark:text-slate-400 mt-1 w-full line-clamp-3 leading-normal">{{ $req->complianceRequirement->description }}</p>
                                    @endif
                                </div>

                                {{-- Show rejection reason if status is rejected --}}
                                @if($req->status === 'rejected' && $req->remarks)
                                    <div class="w-full p-2.5 bg-red-50 dark:bg-red-900/10 border border-red-200/50 dark:border-red-800/30 rounded-xl mb-3">
                                        <p class="text-[11px] font-bold text-red-700 dark:text-red-400 flex items-center gap-1">
                                            <span class="material-symbols-outlined text-[14px]">info</span> Last Rejection Reason:
                                        </p>
                                        <p class="text-[12px] text-red-600 dark:text-red-300 mt-0.5">{{ $req->remarks }}</p>
                                    </div>
                                @endif
                            </div>
                            
                            <div class="mt-2 pt-3 border-t border-[#c2c6d8]/30 dark:border-slate-700 space-y-2">
                                {{-- View Document + Approve/Reject: only when awaiting review --}}
                                @if($isAwaitingReview)
                                    @php
                                        $latestDoc = $req->documents()->latest()->first();
                                    @endphp

                                    @if($latestDoc)
                                        <a href="{{ route('ca.documents.download', $latestDoc->id) }}" target="_blank" class="w-full py-2 bg-white hover:bg-[#f6f3f2] dark:bg-slate-800 dark:hover:bg-slate-700 border border-[#c2c6d8] dark:border-slate-600 text-[#424656] dark:text-slate-300 rounded-lg text-[13px] font-semibold transition-colors flex items-center justify-center gap-2 cursor-pointer">
                                            <span class="material-symbols-outlined text-[18px]">visibility</span> View Document
                                        </a>
                                    @endif

                                    <div class="flex gap-2">
                                        <button wire:click="approveRequirement({{ $req->id }})" class="flex-1 py-2 bg-[#006a61] hover:bg-[#00554e] text-white rounded-lg text-[13px] font-semibold transition-colors flex items-center justify-center gap-1 cursor-pointer">
                                            <span class="material-symbols-outlined text-[16px]">check</span> Approve
                                        </button>
                                        <button wire:click="openRejectModal({{ $req->id }})" class="flex-1 py-2 bg-[#ba1a1a] hover:bg-[#93000a] text-white rounded-lg text-[13px] font-semibold transition-colors flex items-center justify-center gap-1 cursor-pointer">
                                            <span class="material-symbols-outlined text-[16px]">close</span> Reject
                                        </button>
                                    </div>
                                @elseif($req->status === 'pending' || $req->status === 'rejected')
                                    @if($hasAutomation)
                                        <div class="flex items-center gap-1.5 text-xs text-green-700 dark:text-green-400 font-medium mb-3 justify-center">
                                            <span class="material-symbols-outlined text-[16px]">smart_toy</span>
                                            Automated reminders are active
                                        </div>
                                    @endif
                                    <button wire:click="redirectToDM" class="w-full py-2.5 bg-[#f6f3f2] dark:bg-slate-900 text-[#1c1b1b] dark:text-white rounded-lg text-[13px] font-semibold transition-all shadow-sm flex items-center justify-center gap-2 border border-[#c2c6d8]/50 dark:border-slate-700 cursor-pointer">
                                        <svg class="w-4 h-4" fill="#25D366" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 0 0-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/></svg> Send WhatsApp Reminder
                                    </button>
                                @elseif($req->status === 'approved')
                                    <div class="flex items-center justify-center gap-2 py-2 text-[13px] font-medium text-emerald-700 dark:text-emerald-400">
                                        <span class="material-symbols-outlined text-[16px]">check_circle</span>
                                        Approved — awaiting next cycle
                                    </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="col-span-1 md:col-span-2 text-center py-12 bg-[#f6f3f2]/50 dark:bg-slate-900/50 rounded-xl border-2 border-dashed border-[#c2c6d8] dark:border-slate-700">
                            <div class="w-16 h-16 mx-auto bg-white dark:bg-slate-800 rounded-2xl flex items-center justify-center shadow-sm mb-4">
                                <span class="material-symbols-outlined text-[32px] text-[#727687] dark:text-slate-400">folder_open</span>
                            </div>
                            <h3 class="text-[16px] font-semibold text-[#1c1b1b] dark:text-white mb-2">No Requirements Found</h3>
                            <p class="text-[14px] text-[#424656] dark:text-slate-400">There are no document requirements configured for this compliance.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-4 space-y-6">
            <!-- Completion Progress Card -->
            <div class="bg-white dark:bg-slate-800 border border-[#c2c6d8] dark:border-slate-700 rounded-2xl p-6 shadow-sm">
                <h3 class="font-semibold text-[#1c1b1b] dark:text-white mb-6">Recurring Completion Progress</h3>
                
                <div class="mb-6">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[13px] font-medium text-[#424656] dark:text-slate-400">Overall Progress</span>
                        <span class="text-[14px] font-bold text-primary dark:text-blue-400">{{ $sidebarPercentage }}%</span>
                    </div>
                    <div class="w-full h-2 bg-[#f6f3f2] dark:bg-slate-900 rounded-full overflow-hidden">
                        <div class="h-full bg-primary dark:bg-blue-500 rounded-full transition-all duration-1000" style="width: {{ $sidebarPercentage }}%"></div>
                    </div>
                </div>
                
                <div class="space-y-3">
                    <div class="flex items-center justify-between p-3 rounded-xl bg-white dark:bg-slate-800 border border-[#c2c6d8]/30 dark:border-slate-700">
                        <div class="flex items-center gap-3">
                            <span class="w-2.5 h-2.5 rounded-full bg-[#006a61] dark:bg-teal-400"></span>
                            <span class="text-[13px] font-medium text-[#424656] dark:text-slate-300">Approved (this cycle)</span>
                        </div>
                        <span class="font-semibold text-[#1c1b1b] dark:text-white">{{ $sidebarApproved }}</span>
                    </div>
                    <div class="flex items-center justify-between p-3 rounded-xl bg-white dark:bg-slate-800 border border-[#c2c6d8]/30 dark:border-slate-700">
                        <div class="flex items-center gap-3">
                            <span class="w-2.5 h-2.5 rounded-full bg-primary dark:bg-blue-400"></span>
                            <span class="text-[13px] font-medium text-[#424656] dark:text-slate-300">In Review</span>
                        </div>
                        <span class="font-semibold text-[#1c1b1b] dark:text-white">{{ $sidebarInReview }}</span>
                    </div>
                    <div class="flex items-center justify-between p-3 rounded-xl bg-white dark:bg-slate-800 border border-[#c2c6d8]/30 dark:border-slate-700">
                        <div class="flex items-center gap-3">
                            <span class="w-2.5 h-2.5 rounded-full border-2 border-[#727687] dark:border-slate-500"></span>
                            <span class="text-[13px] font-medium text-[#424656] dark:text-slate-300">Pending / Rejected</span>
                        </div>
                        <span class="font-semibold text-[#1c1b1b] dark:text-white">{{ $sidebarPending }}</span>
                    </div>
                </div>
            </div>

            <!-- Deadlines Card -->
            <div class="bg-white dark:bg-slate-800 border border-[#c2c6d8] dark:border-slate-700 rounded-2xl p-6 shadow-sm">
                <h3 class="font-semibold text-[#1c1b1b] dark:text-white mb-6 flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#ba1a1a] dark:text-red-500">event_busy</span>
                    Upcoming Deadlines
                </h3>
                
                <div class="space-y-4">
                    @forelse($clientCompliance->deadlines()->whereHas('clientComplianceRequirement', function($q) { $q->where('is_recurring', true); })->where('status', '!=', 'completed')->orderBy('due_date')->take(5)->get() as $deadline)
                        @php
                            $isOverdue = $deadline->due_date->isPast();
                        @endphp
                        <div class="flex items-start justify-between p-3 rounded-xl border {{ $isOverdue ? 'bg-[#ffdad6]/30 border-[#ffdad6] dark:bg-red-900/10 dark:border-red-900/30' : 'bg-[#f6f3f2]/50 border-[#c2c6d8]/30 dark:bg-slate-900/50 dark:border-slate-700' }}">
                            <div>
                                <p class="text-[13px] font-semibold {{ $isOverdue ? 'text-[#93000a] dark:text-red-400' : 'text-[#1c1b1b] dark:text-white' }} mb-0.5">
                                    {{ $deadline->deadline_name }}
                                </p>
                                <p class="text-[11px] font-medium text-[#727687] dark:text-slate-500 uppercase tracking-wider">
                                    {{ $deadline->deadline_type }}
                                </p>
                            </div>
                            <div class="text-right shrink-0 ml-3">
                                <p class="text-[13px] font-bold {{ $isOverdue ? 'text-[#ba1a1a] dark:text-red-500' : 'text-primary dark:text-blue-400' }}">
                                    {{ $deadline->due_date->format('M d, Y') }}
                                </p>
                                <span class="text-[10px] font-medium {{ $isOverdue ? 'text-[#93000a] dark:text-red-400' : 'text-[#424656] dark:text-slate-400' }}">
                                    {{ $isOverdue ? 'Overdue by ' . $deadline->due_date->diffInDays() . ' days' : $deadline->due_date->diffForHumans() }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4 text-[13px] text-[#727687] dark:text-slate-400">
                            No specific deadlines set.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Timeline Card -->
            <div class="bg-white dark:bg-slate-800 border border-[#c2c6d8] dark:border-slate-700 rounded-2xl p-6 shadow-sm">
                <h3 class="font-semibold text-[#1c1b1b] dark:text-white mb-6">Activity Timeline</h3>
                
                <div class="relative pl-6 space-y-8 before:content-[''] before:absolute before:left-2.5 before:top-2 before:bottom-2 before:w-0.5 before:bg-[#c2c6d8]/50 dark:before:bg-slate-700">
                    @forelse($clientCompliance->timelines->take(5) as $timeline)
                        <div class="relative">
                            <div class="absolute -left-[27px] top-1 w-3.5 h-3.5 rounded-full bg-primary dark:bg-blue-500 ring-4 ring-white dark:ring-slate-800"></div>
                            <p class="text-[14px] font-medium text-[#1c1b1b] dark:text-white mb-1">{{ $timeline->title }}</p>
                            <p class="text-[13px] text-[#424656] dark:text-slate-400 leading-snug">{{ $timeline->description }}</p>
                            <p class="text-[11px] font-medium text-[#727687] dark:text-slate-500 mt-2">{{ $timeline->created_at->diffForHumans() }}</p>
                        </div>
                    @empty
                        <div class="text-center py-6 text-[13px] text-[#727687] dark:text-slate-400">
                            No recent activity.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Recurrence Configuration Modal -->
    @if($showRecurrenceModal && $editingRequirementId)
        <div class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm animate-fade-in">
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-2xl overflow-hidden border border-[#c2c6d8]/50 dark:border-slate-700 flex flex-col max-h-[90vh]">
                <!-- Modal Header -->
                <div class="px-6 py-4 border-b border-[#c2c6d8]/50 dark:border-slate-700 flex items-center justify-between bg-slate-50 dark:bg-slate-800/50">
                    <h3 class="text-lg font-bold text-[#1c1b1b] dark:text-white flex items-center gap-2">
                        <span class="material-symbols-outlined text-teal-600 dark:text-teal-400">calendar_month</span>
                        Configure Collection Schedule
                    </h3>
                    <button type="button" wire:click="closeRecurrenceModal" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6 overflow-y-auto flex-1 space-y-6">
                    <!-- Configured Schedules List -->
                    @if(!empty($configureSchedules))
                        <div>
                            <label class="block text-sm font-bold text-[#424656] dark:text-slate-300 mb-3">Configured Schedules</label>
                            <div class="space-y-2">
                                @foreach($configureSchedules as $index => $sched)
                                    @php
                                        $schedFreq = $sched['frequency'] ?? '';
                                        $schedConf = $sched['config'] ?? [];
                                        $desc = '';
                                        if ($schedFreq === 'daily') {
                                            $desc = 'Daily at ' . ($schedConf['time'] ?? '09:00');
                                        } elseif ($schedFreq === 'weekly') {
                                            $desc = 'Weekly on ' . implode(', ', $schedConf['days'] ?? []);
                                        } elseif ($schedFreq === 'monthly') {
                                            $desc = 'Monthly on day ' . ($schedConf['day_of_month'] ?? '1');
                                        } elseif ($schedFreq === 'quarterly') {
                                            $desc = 'Quarterly (' . ($schedConf['quarter_type'] ?? 'calendar') . ')';
                                        } elseif ($schedFreq === 'yearly') {
                                            $desc = 'Yearly on ' . ($schedConf['day'] ?? '1') . '/' . ($schedConf['month'] ?? '1');
                                        }
                                    @endphp
                                    <div class="flex items-center justify-between p-3 bg-slate-50 dark:bg-slate-900 rounded-xl border border-[#c2c6d8]/50 dark:border-slate-700">
                                        <div class="flex items-center gap-2">
                                            <span class="material-symbols-outlined text-teal-600 dark:text-teal-400 text-[18px]">event_repeat</span>
                                            <span class="text-sm font-medium text-[#1c1b1b] dark:text-white capitalize">{{ $schedFreq }}:</span>
                                            <span class="text-sm text-[#424656] dark:text-slate-300 font-semibold">{{ $desc }}</span>
                                        </div>
                                        <button type="button" wire:click="removeSchedule({{ $index }})" class="text-red-500 hover:text-red-700 dark:hover:text-red-400 transition-colors p-1 flex items-center justify-center cursor-pointer">
                                            <span class="material-symbols-outlined text-[18px]">delete</span>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="border-t border-[#c2c6d8]/30 dark:border-slate-700/50 pt-4">
                        <h4 class="text-sm font-bold text-[#1c1b1b] dark:text-white mb-3">Add Schedule Option</h4>
                        
                        <div>
                            <label class="block text-xs font-bold text-[#424656] dark:text-slate-400 uppercase mb-2">Frequency</label>
                            <select wire:model.live="configureFrequency" class="w-full text-sm bg-[#f6f3f2] dark:bg-slate-900 border border-[#c2c6d8]/50 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-teal-500 py-3 px-4 text-[#1c1b1b] dark:text-white">
                                <option value="">Select frequency...</option>
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="monthly">Monthly</option>
                                <option value="quarterly">Quarterly</option>
                                <option value="yearly">Yearly</option>
                            </select>
                            @error('configureFrequency') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="mt-4">
                            @if($configureFrequency === 'daily')
                                <div>
                                    <label class="block text-sm font-bold text-[#424656] dark:text-slate-300 mb-2">Time of Day</label>
                                    <input type="time" onclick="this.showPicker()" wire:model.live="configureConfig.time" class="w-full text-sm bg-[#f6f3f2] dark:bg-slate-900 border border-[#c2c6d8]/50 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-teal-500 py-3 px-4 text-[#1c1b1b] dark:text-white cursor-pointer">
                                    @error('configureConfig.time') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                </div>
                            @elseif($configureFrequency === 'weekly')
                                <div>
                                    <label class="block text-sm font-bold text-[#424656] dark:text-slate-300 mb-3">Select Day(s) of the Week</label>
                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                        @foreach(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $day)
                                            <label class="flex items-center gap-2 p-3 bg-[#f6f3f2] dark:bg-slate-900 rounded-xl border border-[#c2c6d8]/50 dark:border-slate-700 cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                                                <input type="checkbox" wire:model.live="configureConfig.days" value="{{ $day }}" class="text-teal-600 rounded border-[#c2c6d8] dark:border-slate-600 bg-white dark:bg-slate-800 focus:ring-teal-500">
                                                <span class="text-sm font-medium text-[#1c1b1b] dark:text-white">{{ $day }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                    @error('configureConfig.days') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                </div>
                            @elseif($configureFrequency === 'monthly')
                                <div>
                                    <label class="block text-sm font-bold text-[#424656] dark:text-slate-300 mb-2">Day of the Month</label>
                                    <select wire:model.live="configureConfig.day_of_month" class="w-full text-sm bg-[#f6f3f2] dark:bg-slate-900 border border-[#c2c6d8]/50 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-teal-500 py-3 px-4 text-[#1c1b1b] dark:text-white">
                                        <option value="">Select day (1-31)</option>
                                        @for($i = 1; $i <= 31; $i++)
                                            <option value="{{ $i }}">{{ $i }}</option>
                                        @endfor
                                    </select>
                                    <p class="text-xs text-slate-500 mt-2 italic">Note: If the selected day does not exist in a month (e.g. 31st in February), the last day of the month will be used.</p>
                                    @error('configureConfig.day_of_month') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                </div>
                            @elseif($configureFrequency === 'quarterly')
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-bold text-[#424656] dark:text-slate-300 mb-2">Quarter Type</label>
                                        <select wire:model.live="configureConfig.quarter_type" class="w-full text-sm bg-[#f6f3f2] dark:bg-slate-900 border border-[#c2c6d8]/50 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-teal-500 py-3 px-4 text-[#1c1b1b] dark:text-white">
                                            <option value="">Select quarter type...</option>
                                            <option value="calendar">Calendar (Mar, Jun, Sep, Dec)</option>
                                            <option value="financial">Financial (Jun, Sep, Dec, Mar)</option>
                                        </select>
                                        @error('configureConfig.quarter_type') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-bold text-[#424656] dark:text-slate-300 mb-2">Due Days After Quarter End</label>
                                        <div class="flex items-center gap-2">
                                            <input type="number" min="0" max="90" wire:model.live="configureConfig.due_days_after_quarter_end" class="w-full text-sm bg-[#f6f3f2] dark:bg-slate-900 border border-[#c2c6d8]/50 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-teal-500 py-3 px-4 text-[#1c1b1b] dark:text-white" placeholder="e.g. 15">
                                            <span class="text-sm font-medium text-slate-600 dark:text-slate-400">days</span>
                                        </div>
                                        @error('configureConfig.due_days_after_quarter_end') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            @elseif($configureFrequency === 'yearly')
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-bold text-[#424656] dark:text-slate-300 mb-2">Month</label>
                                        <select wire:model.live="configureConfig.month" class="w-full text-sm bg-[#f6f3f2] dark:bg-slate-900 border border-[#c2c6d8]/50 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-teal-500 py-3 px-4 text-[#1c1b1b] dark:text-white">
                                            <option value="">Select month...</option>
                                            @foreach(['1'=>'Jan','2'=>'Feb','3'=>'Mar','4'=>'Apr','5'=>'May','6'=>'Jun','7'=>'Jul','8'=>'Aug','9'=>'Sep','10'=>'Oct','11'=>'Nov','12'=>'Dec'] as $num => $mon)
                                                <option value="{{ $num }}">{{ $mon }}</option>
                                            @endforeach
                                        </select>
                                        @error('configureConfig.month') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-bold text-[#424656] dark:text-slate-300 mb-2">Day</label>
                                        <select wire:model.live="configureConfig.day" class="w-full text-sm bg-[#f6f3f2] dark:bg-slate-900 border border-[#c2c6d8]/50 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-teal-500 py-3 px-4 text-[#1c1b1b] dark:text-white">
                                            <option value="">Select day...</option>
                                            @for($i = 1; $i <= 31; $i++)
                                                <option value="{{ $i }}">{{ $i }}</option>
                                            @endfor
                                        </select>
                                        @error('configureConfig.day') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            @endif
                        </div>

                        @if(!empty($configureFrequency))
                            <button type="button" wire:click="addSchedule" class="mt-4 px-4 py-2.5 bg-teal-600 text-white font-bold text-xs rounded-xl hover:bg-teal-700 transition-colors shadow-sm flex items-center justify-center gap-1.5 cursor-pointer">
                                <span class="material-symbols-outlined text-[16px]">add</span>
                                Add Option to Schedule
                            </button>
                        @endif
                    </div>

                    <!-- Live Preview -->
                    <div class="mt-8 p-4 bg-teal-50 dark:bg-teal-900/20 border border-teal-200 dark:border-teal-800/30 rounded-xl flex items-start gap-3">
                        <span class="material-symbols-outlined text-teal-600 dark:text-teal-400 mt-0.5">event_available</span>
                        <div>
                            <h4 class="text-sm font-bold text-teal-800 dark:text-teal-300">Live Schedule Preview</h4>
                            @if($configureNextDueDatePreview)
                                <p class="text-sm text-teal-700 dark:text-teal-400 mt-1">Based on this rule, the next due date will be generated as: <span class="font-bold text-[#1c1b1b] dark:text-white">{{ $configureNextDueDatePreview }}</span></p>
                            @else
                                <p class="text-sm text-teal-700/70 dark:text-teal-400/70 mt-1 italic">Complete configuration to see preview.</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="px-6 py-4 border-t border-[#c2c6d8]/50 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex justify-end gap-3">
                    <button type="button" wire:click="closeRecurrenceModal" class="px-6 py-2.5 cursor-pointer rounded-xl border border-[#c2c6d8]/50 dark:border-slate-700 hover:bg-white dark:hover:bg-slate-700 transition-colors font-semibold text-[#424656] dark:text-slate-300">
                        Cancel
                    </button>
                    <button type="button" wire:click="saveRecurrenceConfig" class="px-6 py-2.5 cursor-pointer bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold shadow-md shadow-blue-600/20 hover:-translate-y-0.5 transition-all flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px]">save</span>
                        Save Configuration
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Rejection Reason Modal -->
    @if($showRejectModal && $rejectingRequirementId)
        @php
            $rejectingReq = $clientCompliance->clientRequirements->firstWhere('id', $rejectingRequirementId);
        @endphp
        <div class="fixed inset-0 z-[110] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden border border-[#c2c6d8]/50 dark:border-slate-700">
                <!-- Modal Header -->
                <div class="px-6 py-4 border-b border-red-200/50 dark:border-red-900/30 bg-red-50 dark:bg-red-900/10 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-red-800 dark:text-red-400 flex items-center gap-2">
                        <span class="material-symbols-outlined">do_not_disturb_on</span>
                        Reject Document
                    </h3>
                    <button type="button" wire:click="closeRejectModal" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors cursor-pointer">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6 space-y-4">
                    @if($rejectingReq)
                        <div class="p-3 bg-slate-50 dark:bg-slate-900 rounded-xl border border-[#c2c6d8]/30 dark:border-slate-700">
                            <p class="text-[12px] font-bold text-[#727687] dark:text-slate-400 uppercase tracking-wider">Rejecting</p>
                            <p class="text-[15px] font-semibold text-[#1c1b1b] dark:text-white mt-0.5">{{ $rejectingReq->name }}</p>
                            <p class="text-[12px] text-[#424656] dark:text-slate-400 mt-0.5">Client: {{ $client->client_name }}</p>
                        </div>
                    @endif

                    <div>
                        <label class="block text-sm font-bold text-[#424656] dark:text-slate-300 mb-2">
                            Reason for Rejection <span class="text-red-500">*</span>
                        </label>
                        <textarea 
                            wire:model.defer="rejectionReason" 
                            rows="4" 
                            class="w-full text-sm bg-[#f6f3f2] dark:bg-slate-900 border border-[#c2c6d8]/50 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-red-500 focus:border-red-500 py-3 px-4 text-[#1c1b1b] dark:text-white resize-none placeholder:text-slate-400"
                            placeholder="e.g. The document is blurry and unreadable. Please resubmit a clear scan of your GST Sales Invoices."
                        ></textarea>
                        @error('rejectionReason') 
                            <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> 
                        @enderror
                        <p class="text-[11px] text-[#727687] dark:text-slate-500 mt-1.5">
                            <span class="material-symbols-outlined text-[13px] align-middle">info</span>
                            This reason will be sent to the client via WhatsApp so they know what to fix.
                        </p>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="px-6 py-4 border-t border-[#c2c6d8]/50 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex justify-end gap-3">
                    <button type="button" wire:click="closeRejectModal" class="px-5 py-2.5 cursor-pointer rounded-xl border border-[#c2c6d8]/50 dark:border-slate-700 hover:bg-white dark:hover:bg-slate-700 transition-colors font-semibold text-[#424656] dark:text-slate-300">
                        Cancel
                    </button>
                    <button type="button" wire:click="confirmRejectRequirement" class="px-5 py-2.5 cursor-pointer bg-[#ba1a1a] hover:bg-[#93000a] text-white rounded-xl font-bold shadow-md shadow-red-600/20 hover:-translate-y-0.5 transition-all flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px]">send</span>
                        Send Rejection & Notify Client
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
