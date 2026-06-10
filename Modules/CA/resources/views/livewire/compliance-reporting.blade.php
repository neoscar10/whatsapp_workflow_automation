<div class="p-6 lg:p-8 w-full max-w-7xl mx-auto font-['Inter']">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
        <div>
            <h1 class="text-[24px] font-semibold tracking-tight text-primary dark:text-blue-400">Compliance Reporting</h1>
            <p class="text-[14px] text-[#424656] dark:text-slate-400 mt-1">Track and export compliance status across all clients.</p>
        </div>
        <button class="inline-flex items-center gap-2 px-6 py-2.5 bg-primary hover:bg-primary/90 text-white rounded-lg font-medium text-[14px] transition-all shadow-md active:scale-95">
            <span class="material-symbols-outlined text-[20px]">download</span>
            Export Report
        </button>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-slate-800 border border-[#c2c6d8] dark:border-slate-700 rounded-2xl p-6 shadow-sm mb-8">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
            <div class="md:col-span-4">
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-[#727687] dark:text-slate-400">filter_list</span>
                    <select wire:model.live="filterStatus" class="w-full pl-12 pr-4 py-3 bg-[#f6f3f2] dark:bg-slate-900 border-none rounded-xl focus:ring-2 focus:ring-primary/20 text-[14px] text-[#1c1b1b] dark:text-white transition-all appearance-none cursor-pointer">
                        <option value="">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="in_progress">In Progress</option>
                        <option value="completed">Completed</option>
                        <option value="overdue">Overdue</option>
                        <option value="blocked">Blocked</option>
                        <option value="at_risk">At Risk</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="bg-white dark:bg-slate-800 border border-[#c2c6d8]/50 dark:border-slate-700 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#f6f3f2] dark:bg-slate-900/50 border-b border-[#c2c6d8]/50 dark:border-slate-700">
                        <th class="px-6 py-4 text-[12px] font-['Geist'] font-medium tracking-[0.02em] text-[#727687] dark:text-slate-400 uppercase">Client Name</th>
                        <th class="px-6 py-4 text-[12px] font-['Geist'] font-medium tracking-[0.02em] text-[#727687] dark:text-slate-400 uppercase">Compliance</th>
                        <th class="px-6 py-4 text-[12px] font-['Geist'] font-medium tracking-[0.02em] text-[#727687] dark:text-slate-400 uppercase">Assigned Date</th>
                        <th class="px-6 py-4 text-[12px] font-['Geist'] font-medium tracking-[0.02em] text-[#727687] dark:text-slate-400 uppercase">Health Status</th>
                        <th class="px-6 py-4 text-[12px] font-['Geist'] font-medium tracking-[0.02em] text-[#727687] dark:text-slate-400 uppercase text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#c2c6d8]/30 dark:divide-slate-700">
                    @forelse($compliances as $compliance)
                        <tr class="hover:bg-[#f6f3f2]/50 dark:hover:bg-slate-800/50 transition-colors group">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg bg-primary/10 dark:bg-primary/20 flex items-center justify-center text-primary dark:text-blue-400 font-bold text-[16px]">
                                        {{ substr($compliance->client->client_name ?? 'C', 0, 1) }}
                                    </div>
                                    <a href="{{ route('ca.clients.show', $compliance->client->id ?? 1) }}" class="text-[14px] font-semibold text-[#1c1b1b] dark:text-white hover:text-primary dark:hover:text-blue-400 transition-colors">
                                        {{ $compliance->client->client_name ?? 'Unknown Client' }}
                                    </a>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-[14px] text-[#424656] dark:text-slate-300">
                                {{ $compliance->compliance->name }}
                            </td>
                            <td class="px-6 py-4 text-[14px] text-[#424656] dark:text-slate-400">
                                {{ $compliance->assigned_at ? $compliance->assigned_at->format('M d, Y') : 'N/A' }}
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $badgeStyle = match($compliance->health_status) {
                                        'completed' => 'bg-[#86f2e4]/30 text-[#006f66] dark:text-teal-400',
                                        'overdue' => 'bg-[#ffdad6] text-[#93000a] dark:bg-red-900/30 dark:text-red-400',
                                        'blocked' => 'bg-slate-200 text-slate-800 dark:bg-slate-700 dark:text-slate-300',
                                        'at_risk' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-500',
                                        'in_progress' => 'bg-primary/10 text-blue-900 dark:bg-primary/20 dark:text-blue-300',
                                        default => 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400'
                                    };
                                    $dotStyle = match($compliance->health_status) {
                                        'completed' => 'bg-[#006a61] dark:bg-teal-400',
                                        'overdue' => 'bg-[#ba1a1a] dark:bg-red-400',
                                        'blocked' => 'bg-slate-600 dark:bg-slate-400',
                                        'at_risk' => 'bg-yellow-600 dark:bg-yellow-400',
                                        'in_progress' => 'bg-primary dark:bg-primary',
                                        default => 'bg-slate-400 dark:bg-slate-500'
                                    };
                                @endphp
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[12px] font-['Geist'] font-medium tracking-wide {{ $badgeStyle }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $dotStyle }}"></span>
                                    {{ str_replace('_', ' ', strtoupper($compliance->health_status)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('ca.clients.compliance.workspace', ['clientId' => $compliance->ca_client_id, 'clientComplianceId' => $compliance->id]) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-[#f6f3f2] dark:bg-slate-900 hover:bg-primary/10 dark:hover:bg-primary/20 text-primary dark:text-blue-400 rounded-lg text-[13px] font-medium transition-colors">
                                    <span class="material-symbols-outlined text-[16px]">folder_open</span>
                                    Workspace
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-16 text-center">
                                <div class="w-16 h-16 mx-auto bg-[#f6f3f2] dark:bg-slate-900 rounded-2xl flex items-center justify-center mb-4">
                                    <span class="material-symbols-outlined text-[32px] text-[#727687] dark:text-slate-400">folder_off</span>
                                </div>
                                <h3 class="text-[18px] font-semibold text-[#1c1b1b] dark:text-white mb-2">No Compliances Found</h3>
                                <p class="text-[14px] text-[#424656] dark:text-slate-400 max-w-sm mx-auto">There are no compliances matching your current filter.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    @if($compliances->hasPages())
        <div class="mt-6">
            {{ $compliances->links() }}
        </div>
    @endif
</div>
