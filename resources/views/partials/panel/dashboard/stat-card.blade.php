<div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 hover:shadow-md transition-shadow">
    <div class="mb-5 flex items-center justify-between">
        <div class="rounded-xl p-3 {{ data_get($stat, 'icon_bg_class') }} {{ data_get($stat, 'icon_text_class') }} shadow-sm">
            <span class="material-symbols-outlined text-2xl">{{ data_get($stat, 'icon') }}</span>
        </div>

        <span class="rounded-full px-3 py-1 text-[11px] font-black uppercase tracking-wider {{ data_get($stat, 'badge_class') }}">
            {{ data_get($stat, 'badge') }}
        </span>
    </div>

    <p class="text-[12px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">
        {{ data_get($stat, 'label') }}
    </p>

    <h3 class="mt-2 text-3xl font-black tracking-tight text-slate-900 dark:text-white">
        {{ data_get($stat, 'value') }}
    </h3>
</div>
