<div class="flex gap-5 group cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-800/50 p-2 -m-2 rounded-xl transition-all">
    <div class="flex size-11 shrink-0 items-center justify-center rounded-2xl {{ data_get($activity, 'icon_bg_class') }} shadow-sm transition-transform group-hover:scale-110">
        <span class="material-symbols-outlined text-[22px] {{ data_get($activity, 'icon_text_class') }}">
            {{ data_get($activity, 'icon') }}
        </span>
    </div>

    <div class="min-w-0 flex-1">
        <p class="text-sm font-bold leading-tight text-slate-900 dark:text-slate-100">
            {{ data_get($activity, 'title') }}
            <span class="font-medium text-slate-500 ml-1">{{ data_get($activity, 'description') }}</span>
        </p>
        <div class="mt-1.5 flex items-center gap-2">
            <span class="material-symbols-outlined text-[14px] text-slate-300">schedule</span>
            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">{{ data_get($activity, 'time') }}</p>
        </div>
    </div>
</div>
