<div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900 mt-8">
    <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-100 p-7 dark:border-slate-800">
        <div>
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Active Campaigns</h3>
            <p class="mt-1 text-xs font-medium text-slate-400 dark:text-slate-500 uppercase tracking-widest">Real-time performance tracking</p>
        </div>

        <div class="flex gap-2">
            <button type="button" class="flex items-center gap-2 rounded-xl bg-primary px-6 py-3 text-sm font-bold text-white shadow-lg shadow-primary/25 transition-all hover:bg-primary/90 hover:scale-[1.02] active:scale-[0.98]">
                <span class="material-symbols-outlined text-[20px]">add_circle</span>
                New Campaign
            </button>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead class="bg-slate-50/50 dark:bg-slate-800/50">
                <tr>
                    <th class="px-8 py-5 text-[10px] font-black uppercase tracking-[0.15em] text-slate-400 dark:text-slate-500">Campaign Details</th>
                    <th class="px-8 py-5 text-[10px] font-black uppercase tracking-[0.15em] text-slate-400 dark:text-slate-500">Status</th>
                    <th class="px-8 py-5 text-[10px] font-black uppercase tracking-[0.15em] text-slate-400 dark:text-slate-500">Sent Volume</th>
                    <th class="px-8 py-5 text-[10px] font-black uppercase tracking-[0.15em] text-slate-400 dark:text-slate-500">Open Rate</th>
                    <th class="px-8 py-5 text-[10px] font-black uppercase tracking-[0.15em] text-slate-400 dark:text-slate-500">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @foreach ($campaigns as $campaign)
                    @include('partials.panel.dashboard.campaign-row', ['campaign' => $campaign])
                @endforeach
            </tbody>
        </table>
    </div>
</div>
