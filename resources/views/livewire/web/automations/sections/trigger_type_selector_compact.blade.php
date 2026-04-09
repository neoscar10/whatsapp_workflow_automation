<div class="space-y-4">
    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Trigger Category</label>
    
    @php
        $category = $nodeConfig['trigger_category'] ?? 'webhook_api';
        $categoryLabel = match($category) {
            'webhook_api' => 'Webhook / API Trigger',
            'time_based' => 'Time-based (Scheduled)',
            'event_based' => 'Event-based (System)',
            'behavior_based' => 'Behavior-based (Activity)',
            'conditional' => 'Conditional rules',
            default => ucfirst(str_replace('_', ' ', $category))
        };
        $categoryIcon = match($category) {
            'webhook_api' => 'hub',
            'time_based' => 'schedule',
            'event_based' => 'notifications_active',
            'behavior_based' => 'track_changes',
            'conditional' => 'rule',
            default => 'rocket_launch'
        };
    @endphp

    <div class="flex items-center gap-4 p-4 bg-white/5 border border-white/10 rounded-2xl group transition-all hover:bg-white/[0.07]">
        <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center border border-primary/20 shadow-lg shadow-primary/5">
            <span class="material-symbols-outlined text-primary text-xl">{{ $categoryIcon }}</span>
        </div>
        <div class="flex-1">
            <p class="text-[11px] font-black text-white uppercase tracking-wider">{{ $categoryLabel }}</p>
            <p class="text-[9px] font-bold text-slate-500 uppercase tracking-tight">Core Type • Non-editable</p>
        </div>
    </div>
</div>
