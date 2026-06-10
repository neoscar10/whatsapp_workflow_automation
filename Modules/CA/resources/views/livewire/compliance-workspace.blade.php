<div class="p-6 lg:p-8 w-full max-w-7xl mx-auto font-['Inter']">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
        <div>
            <div class="flex items-center gap-2 text-[14px] text-[#424656] dark:text-slate-400 mb-2">
                <a href="{{ route('ca.clients.show', $clientCompliance->client->id) }}" class="hover:text-[#0050cb] dark:hover:text-indigo-400 transition-colors">
                    {{ $clientCompliance->client->client_name }}
                </a>
                <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                <span class="font-medium text-[#1c1b1b] dark:text-slate-300">Workspace</span>
            </div>
            <h1 class="text-[24px] font-semibold tracking-tight text-[#0050cb] dark:text-indigo-400">
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
                <h2 class="text-[18px] font-semibold text-[#1c1b1b] dark:text-white mb-6 flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#0050cb] dark:text-indigo-400">folder_open</span>
                    Document Requirements
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @forelse($clientCompliance->clientRequirements as $req)
                        <div class="group bg-white dark:bg-slate-800 rounded-xl border {{ $req->status === 'approved' ? 'border-[#006a61]/30 dark:border-teal-500/30' : 'border-[#c2c6d8]/50 dark:border-slate-700' }} p-5 transition-all cursor-pointer relative overflow-hidden shadow-sm hover:shadow-md hover:border-[#0050cb]/30 flex flex-col justify-between h-full">
                            
                            <div>
                                <div class="flex items-start justify-between mb-4">
                                    <div>
                                        <span class="px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider mb-2 inline-block
                                            {{ $req->status === 'approved' ? 'bg-[#86f2e4]/30 text-[#006f66] dark:text-teal-400' : 
                                               ($req->status === 'uploaded' ? 'bg-[#dae1ff] text-[#001849] dark:bg-indigo-900/30 dark:text-indigo-400' : 
                                               'bg-[#f6f3f2] text-[#424656] dark:bg-slate-700 dark:text-slate-300') }}">
                                            {{ $req->status }}
                                        </span>
                                        @php
                                            $displayDueDate = $req->due_date ?? $clientCompliance->deadlines->where('status', 'pending')->sortBy('due_date')->first()->due_date ?? null;
                                        @endphp
                                        @if($displayDueDate)
                                            @php
                                                $parsedDate = \Carbon\Carbon::parse($displayDueDate);
                                            @endphp
                                            <span class="px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider mb-2 inline-flex items-center gap-1 ml-2
                                                {{ $parsedDate->isPast() && $req->status !== 'approved' ? 'bg-[#ffdad6] text-[#93000a] dark:bg-red-900/30 dark:text-red-400' : 'bg-[#f6f3f2] text-[#424656] dark:bg-slate-800 dark:text-slate-400' }}">
                                                <span class="material-symbols-outlined text-[12px]">calendar_today</span>
                                                Due: {{ $parsedDate->format('M d, Y') }}
                                            </span>
                                        @endif
                                        <h4 class="font-semibold text-[#1c1b1b] dark:text-white text-[15px] leading-tight">{{ $req->name ?? $req->complianceRequirement->name ?? 'Requirement' }}</h4>
                                        <p class="text-[12px] text-[#727687] dark:text-slate-400 mt-1 line-clamp-2">{{ $req->complianceRequirement->description ?? '' }}</p>
                                    </div>
                                    
                                    @if($req->status === 'approved')
                                        <div class="w-8 h-8 rounded-full bg-[#006a61]/10 flex items-center justify-center text-[#006a61] dark:text-teal-400 shrink-0">
                                            <span class="material-symbols-outlined text-[20px]">verified</span>
                                        </div>
                                    @elseif($req->status === 'uploaded')
                                        <div class="w-8 h-8 rounded-full bg-[#0050cb]/10 flex items-center justify-center text-[#0050cb] dark:text-indigo-400 shrink-0">
                                            <span class="material-symbols-outlined text-[20px]">hourglass_top</span>
                                        </div>
                                    @else
                                        <div class="w-8 h-8 rounded-full bg-[#f6f3f2] dark:bg-slate-700 flex items-center justify-center text-[#727687] dark:text-slate-400 shrink-0">
                                            <span class="material-symbols-outlined text-[20px]">upload_file</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="mt-4 pt-4 border-t border-[#c2c6d8]/30 dark:border-slate-700">
                                @if($req->status === 'pending' || $req->status === 'rejected')
                                    <button class="w-full py-2 bg-[#f6f3f2] hover:bg-[#e5e2e1] dark:bg-slate-900 dark:hover:bg-slate-700 text-[#0050cb] dark:text-indigo-400 rounded-lg text-[13px] font-semibold transition-colors flex items-center justify-center gap-2">
                                        <span class="material-symbols-outlined text-[18px]">cloud_upload</span> Request Upload
                                    </button>
                                @elseif($req->status === 'uploaded')
                                    <div class="flex gap-2">
                                        <button class="flex-1 py-2 bg-[#006a61] hover:bg-[#00554e] text-white rounded-lg text-[13px] font-semibold transition-colors flex items-center justify-center gap-1">
                                            <span class="material-symbols-outlined text-[16px]">check</span> Approve
                                        </button>
                                        <button class="flex-1 py-2 bg-[#ba1a1a] hover:bg-[#93000a] text-white rounded-lg text-[13px] font-semibold transition-colors flex items-center justify-center gap-1">
                                            <span class="material-symbols-outlined text-[16px]">close</span> Reject
                                        </button>
                                    </div>
                                @else
                                    <button class="w-full py-2 bg-white hover:bg-[#f6f3f2] dark:bg-slate-800 dark:hover:bg-slate-700 border border-[#c2c6d8] dark:border-slate-600 text-[#424656] dark:text-slate-300 rounded-lg text-[13px] font-semibold transition-colors flex items-center justify-center gap-2">
                                        <span class="material-symbols-outlined text-[18px]">visibility</span> View Document
                                    </button>
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
            <!-- Progress Card -->
            <div class="bg-white dark:bg-slate-800 border border-[#c2c6d8] dark:border-slate-700 rounded-2xl p-6 shadow-sm">
                <h3 class="font-semibold text-[#1c1b1b] dark:text-white mb-6">Completion Progress</h3>
                
                @php
                    $total = $clientCompliance->clientRequirements->count();
                    $approved = $clientCompliance->clientRequirements->where('status', 'approved')->count();
                    $percentage = $total > 0 ? round(($approved / $total) * 100) : 0;
                @endphp
                
                <div class="mb-6">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[13px] font-medium text-[#424656] dark:text-slate-400">Overall Progress</span>
                        <span class="text-[14px] font-bold text-[#0050cb] dark:text-indigo-400">{{ $percentage }}%</span>
                    </div>
                    <div class="w-full h-2 bg-[#f6f3f2] dark:bg-slate-900 rounded-full overflow-hidden">
                        <div class="h-full bg-[#0050cb] dark:bg-indigo-500 rounded-full transition-all duration-1000" style="width: {{ $percentage }}%"></div>
                    </div>
                </div>
                
                <div class="space-y-3">
                    <div class="flex items-center justify-between p-3 rounded-xl bg-white dark:bg-slate-800 border border-[#c2c6d8]/30 dark:border-slate-700">
                        <div class="flex items-center gap-3">
                            <span class="w-2.5 h-2.5 rounded-full bg-[#006a61] dark:bg-teal-400"></span>
                            <span class="text-[13px] font-medium text-[#424656] dark:text-slate-300">Approved</span>
                        </div>
                        <span class="font-semibold text-[#1c1b1b] dark:text-white">{{ $approved }}</span>
                    </div>
                    <div class="flex items-center justify-between p-3 rounded-xl bg-white dark:bg-slate-800 border border-[#c2c6d8]/30 dark:border-slate-700">
                        <div class="flex items-center gap-3">
                            <span class="w-2.5 h-2.5 rounded-full bg-[#0050cb] dark:bg-indigo-400"></span>
                            <span class="text-[13px] font-medium text-[#424656] dark:text-slate-300">In Review</span>
                        </div>
                        <span class="font-semibold text-[#1c1b1b] dark:text-white">{{ $clientCompliance->clientRequirements->where('status', 'uploaded')->count() }}</span>
                    </div>
                    <div class="flex items-center justify-between p-3 rounded-xl bg-white dark:bg-slate-800 border border-[#c2c6d8]/30 dark:border-slate-700">
                        <div class="flex items-center gap-3">
                            <span class="w-2.5 h-2.5 rounded-full border-2 border-[#727687] dark:border-slate-500"></span>
                            <span class="text-[13px] font-medium text-[#424656] dark:text-slate-300">Pending</span>
                        </div>
                        <span class="font-semibold text-[#1c1b1b] dark:text-white">{{ $clientCompliance->clientRequirements->where('status', 'pending')->count() }}</span>
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
                    @forelse($clientCompliance->deadlines()->orderBy('due_date')->get() as $deadline)
                        @php
                            $isOverdue = $deadline->due_date->isPast() && $deadline->status !== 'completed';
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
                                <p class="text-[13px] font-bold {{ $isOverdue ? 'text-[#ba1a1a] dark:text-red-500' : 'text-[#0050cb] dark:text-indigo-400' }}">
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
                    @forelse($clientCompliance->timelines ?? [] as $timeline)
                        <div class="relative">
                            <div class="absolute -left-[27px] top-1 w-3.5 h-3.5 rounded-full bg-[#0050cb] dark:bg-indigo-500 ring-4 ring-white dark:ring-slate-800"></div>
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
</div>
