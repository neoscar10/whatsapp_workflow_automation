<div class="flex flex-col rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900 min-h-[500px]">
    <div class="border-b border-slate-100 p-6 dark:border-slate-800 flex items-center justify-between">
        <h3 class="text-lg font-bold text-slate-900 dark:text-white">Recent Activity</h3>
        <span class="size-6 rounded-full bg-slate-50 dark:bg-slate-800 flex items-center justify-center text-[10px] font-bold text-slate-400">
            {{ count($activities) }}
        </span>
    </div>

    <div class="flex-1 space-y-7 overflow-y-auto p-7">
        @foreach ($activities as $activity)
            @include('partials.panel.dashboard.activity-item', ['activity' => $activity])
        @endforeach
    </div>

    <div class="bg-slate-50/80 p-5 text-center dark:bg-slate-800/30 border-t border-slate-100 dark:border-slate-800">
        <button type="button" class="text-sm font-bold text-primary hover:text-primary transition-all hover:underline decoration-2 underline-offset-4">View All Activity</button>
    </div>
</div>
