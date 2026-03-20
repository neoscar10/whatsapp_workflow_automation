<div class="flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900 lg:col-span-2">
    <div class="flex items-center justify-between border-b border-slate-100 p-6 dark:border-slate-800">
        <div>
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">{{ data_get($chart, 'title') }}</h3>
            <p class="text-[12px] font-medium text-slate-400 dark:text-slate-500">{{ data_get($chart, 'subtitle') }}</p>
        </div>

        <div class="flex gap-1 bg-slate-50 dark:bg-slate-800 p-1 rounded-xl">
            <button type="button" class="rounded-lg bg-white shadow-sm px-4 py-1.5 text-xs font-bold text-primary dark:bg-slate-700 dark:text-white transition-all">
                Week
            </button>
            <button type="button" class="rounded-lg px-4 py-1.5 text-xs font-bold text-slate-400 hover:text-slate-600 dark:text-slate-500 transition-all">
                Month
            </button>
        </div>
    </div>

    <div class="flex flex-1 flex-col p-8">
        <div class="mb-8 flex items-end gap-3">
            <h4 class="text-4xl font-black tracking-tight text-slate-900 dark:text-white leading-none">
                {{ data_get($chart, 'total') }}
            </h4>

            <div class="flex items-center gap-1.5 mb-1 px-2 py-0.5 rounded-full bg-green-50 dark:bg-green-900/20">
                <span class="material-symbols-outlined text-[18px] text-green-500">trending_up</span>
                <span class="text-xs font-black text-green-600 dark:text-green-500 uppercase">{{ data_get($chart, 'change') }}</span>
            </div>
            <span class="mb-1.5 text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">{{ data_get($chart, 'change_label') }}</span>
        </div>

        <div class="relative h-[250px] w-full">
            <svg class="h-full w-full" preserveAspectRatio="none" viewBox="0 0 472 150">
                <defs>
                    <linearGradient id="chartGradient" x1="0" x2="0" y1="0" y2="1">
                        <stop offset="0%" stop-color="#2463eb" stop-opacity="0.25"></stop>
                        <stop offset="100%" stop-color="#2463eb" stop-opacity="0"></stop>
                    </linearGradient>
                </defs>
                <path d="M0 109C18.1538 109 18.1538 21 36.3077 21C54.4615 21 54.4615 41 72.6154 41C90.7692 41 90.7692 93 108.923 93C127.077 93 127.077 33 145.231 33C163.385 33 163.385 101 181.538 101C199.692 101 199.692 61 217.846 61C236 61 236 45 254.154 45C272.308 45 272.308 121 290.462 121C308.615 121 308.615 149 326.769 149C344.923 149 344.923 1 363.077 1C381.231 1 381.231 81 399.385 81C417.538 81 417.538 129 435.692 129C453.846 129 453.846 25 472 25V150H0V109Z" fill="url(#chartGradient)"></path>
                <path d="M0 109C18.1538 109 18.1538 21 36.3077 21C54.4615 21 54.4615 41 72.6154 41C90.7692 41 90.7692 93 108.923 93C127.077 93 127.077 33 145.231 33C163.385 33 163.385 101 181.538 101C199.692 101 199.692 61 217.846 61C236 61 236 45 254.154 45C272.308 45 272.308 121 290.462 121C308.615 121 308.615 149 326.769 149C344.923 149 344.923 1 363.077 1C381.231 1 381.231 81 399.385 81C417.538 81 417.538 129 435.692 129C453.846 129 453.846 25 472 25" fill="none" stroke="#2463eb" stroke-linecap="round" stroke-width="4" filter="drop-shadow(0 4px 6px rgba(36,99,235,0.2))"></path>
            </svg>
        </div>

        <div class="mt-6 flex justify-between px-2">
            @foreach (data_get($chart, 'days', ['MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT', 'SUN']) as $day)
                <span class="text-[10px] font-black tracking-widest {{ $day === 'WED' ? 'text-primary' : 'text-slate-400 dark:text-slate-500' }}">{{ $day }}</span>
            @endforeach
        </div>
    </div>
</div>
