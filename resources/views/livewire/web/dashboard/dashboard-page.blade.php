<div class="mx-auto w-full max-w-[1400px] space-y-8 p-10">
    <div class="flex flex-col gap-2">
        <h2 class="text-3xl font-black tracking-tight text-slate-900 dark:text-white">
            {{ $heading }}
        </h2>
        <p class="text-[15px] font-medium text-slate-500 dark:text-slate-400">
            {{ $subheading }}
        </p>
    </div>

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
</div>
