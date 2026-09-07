<div class="mx-auto w-full max-w-[1400px] space-y-8 p-10">
    <div class="flex flex-col gap-2">
        <h2 class="text-3xl font-black tracking-tight text-slate-900 dark:text-white">
            {{ $heading }}
        </h2>
        <p class="text-[15px] font-medium text-slate-500 dark:text-slate-400">
            {{ $subheading }}
        </p>
    </div>

    {{-- Business Verification Banner --}}
    @php
        $vStatus = $verification['status'] ?? 'not_started';
    @endphp

    @if(in_array($vStatus, ['under_review', 'in_progress', 'partially_approved', 'pending_review']))
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 p-6 text-white shadow-xl shadow-indigo-500/10">
            <div class="absolute -right-10 -top-10 size-40 rounded-full bg-white/10 blur-2xl"></div>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between relative z-10">
                <div class="flex items-start gap-4">
                    <div class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-white/20 backdrop-blur-md">
                        <span class="material-symbols-outlined text-2xl animate-pulse">hourglass_top</span>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-lg font-bold">Business Verification Pending</h3>
                            <span class="rounded-full bg-amber-400/20 px-2.5 py-0.5 text-xs font-bold text-amber-200 border border-amber-300/30">Under Review</span>
                        </div>
                        <p class="mt-1 text-sm text-blue-100 font-medium">
                            Your verification documents have been submitted and are currently being reviewed by our compliance team.
                        </p>
                    </div>
                </div>
                <a href="{{ route('company.verification') }}" class="inline-flex items-center gap-2 rounded-xl bg-white px-5 py-2.5 text-xs font-black text-indigo-700 shadow-md hover:bg-slate-50 transition-all shrink-0">
                    <span>View Verification Status</span>
                    <span class="material-symbols-outlined text-sm">arrow_forward</span>
                </a>
            </div>
        </div>
    @elseif($vStatus === 'not_started')
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-amber-500 via-orange-500 to-amber-600 p-6 text-white shadow-xl shadow-amber-500/10">
            <div class="absolute -right-10 -top-10 size-40 rounded-full bg-white/10 blur-2xl"></div>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between relative z-10">
                <div class="flex items-start gap-4">
                    <div class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-white/20 backdrop-blur-md">
                        <span class="material-symbols-outlined text-2xl">verified_user</span>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-lg font-bold">Complete Business Verification</h3>
                            <span class="rounded-full bg-white/20 px-2.5 py-0.5 text-xs font-bold text-amber-100">Action Required</span>
                        </div>
                        <p class="mt-1 text-sm text-amber-100 font-medium">
                            Submit your business identification documents to complete verification and unlock full production features.
                        </p>
                    </div>
                </div>
                <a href="{{ route('company.verification') }}" class="inline-flex items-center gap-2 rounded-xl bg-white px-5 py-2.5 text-xs font-black text-amber-800 shadow-md hover:bg-amber-50 transition-all shrink-0">
                    <span>Start Verification</span>
                    <span class="material-symbols-outlined text-sm">arrow_forward</span>
                </a>
            </div>
        </div>
    @elseif($vStatus === 'rejected')
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-rose-600 via-red-600 to-pink-600 p-6 text-white shadow-xl shadow-rose-500/10">
            <div class="absolute -right-10 -top-10 size-40 rounded-full bg-white/10 blur-2xl"></div>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between relative z-10">
                <div class="flex items-start gap-4">
                    <div class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-white/20 backdrop-blur-md">
                        <span class="material-symbols-outlined text-2xl">gpp_bad</span>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-lg font-bold">Verification Document Action Required</h3>
                            <span class="rounded-full bg-white/20 px-2.5 py-0.5 text-xs font-bold text-rose-100">Needs Revision</span>
                        </div>
                        <p class="mt-1 text-sm text-rose-100 font-medium">
                            One or more uploaded business verification documents were rejected during compliance review. Please upload updated versions.
                        </p>
                    </div>
                </div>
                <a href="{{ route('company.verification') }}" class="inline-flex items-center gap-2 rounded-xl bg-white px-5 py-2.5 text-xs font-black text-rose-700 shadow-md hover:bg-rose-50 transition-all shrink-0">
                    <span>Fix Documents</span>
                    <span class="material-symbols-outlined text-sm">arrow_forward</span>
                </a>
            </div>
        </div>
    @endif

    @include('partials.panel.dashboard.stats-grid', ['stats' => $stats])

    <div class="grid grid-cols-1 gap-10 lg:grid-cols-3">
        @include('partials.panel.dashboard.activity-chart-card', [
            'chart' => $chart,
        ])

        @include('partials.panel.dashboard.recent-activity-card', [
            'activities' => $activities,
        ])
    </div>

    @include('partials.panel.dashboard.campaigns-table', [
        'campaigns' => $campaigns,
    ])

    <livewire:campaigns.campaign-form-modal />
</div>
