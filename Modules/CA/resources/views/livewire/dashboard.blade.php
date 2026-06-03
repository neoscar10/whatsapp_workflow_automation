<div class="p-8 space-y-6 flex-1 overflow-y-auto">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">CA Dashboard (Proof of Concept)</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">This dashboard is a sandbox verifying dynamic module loading & routing.</p>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm">
        <h3 class="text-base font-bold text-slate-900 dark:text-white mb-2">Module Verification Success!</h3>
        <p class="text-xs text-slate-500 mb-4">If you can see this page, it means:</p>
        <ul class="list-disc list-inside text-xs text-slate-650 space-y-2">
            <li>The <strong>CA Module</strong> routes were successfully loaded dynamically.</li>
            <li>The <code>module:ca</code> tenant-isolation middleware validated your company's assignment of the CA module.</li>
            <li>Livewire v3 component auto-discovery successfully resolved this page class.</li>
        </ul>
    </div>
</div>
