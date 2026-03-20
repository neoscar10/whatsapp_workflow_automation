<tr class="group hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
    <td class="px-8 py-6">
        <div class="flex flex-col">
            <p class="text-[15px] font-bold text-slate-900 dark:text-slate-100 group-hover:text-primary transition-colors">
                {{ data_get($campaign, 'name') }}
            </p>
            <p class="mt-1 text-xs font-medium text-slate-400 dark:text-slate-500 uppercase tracking-wider">
                {{ data_get($campaign, 'category') }}
            </p>
        </div>
    </td>

    <td class="px-8 py-6">
        <span class="inline-flex items-center gap-2 rounded-xl px-3 py-1 text-[11px] font-black uppercase tracking-wider {{ data_get($campaign, 'status_class') }}">
            <span class="size-2 rounded-full {{ data_get($campaign, 'dot_class') }} animate-pulse"></span>
            {{ data_get($campaign, 'status') }}
        </span>
    </td>

    <td class="px-8 py-6 text-sm font-bold text-slate-600 dark:text-slate-300">
        {{ data_get($campaign, 'sent') }}
    </td>

    <td class="px-8 py-6">
        <div class="flex items-center gap-3">
            <span class="text-sm font-bold text-slate-600 dark:text-slate-300">{{ data_get($campaign, 'opened') }}</span>
            @if(data_get($campaign, 'opened') !== '-')
                <div class="h-1.5 w-16 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                    <div class="h-full bg-primary" style="width: {{ data_get($campaign, 'opened') }}"></div>
                </div>
            @endif
        </div>
    </td>

    <td class="px-8 py-6">
        <button type="button" class="flex size-9 items-center justify-center rounded-xl bg-slate-100 text-slate-400 transition-all hover:bg-primary hover:text-white dark:bg-slate-800 dark:hover:bg-primary">
            <span class="material-symbols-outlined text-[20px]">more_horiz</span>
        </button>
    </td>
</tr>
