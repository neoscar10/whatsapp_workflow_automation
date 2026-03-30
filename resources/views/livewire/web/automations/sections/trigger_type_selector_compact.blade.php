<div class="space-y-4">
    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Trigger Type</label>
    <div class="relative group">
        <select 
            wire:model.live="nodeConfig.trigger_category" 
            class="w-full bg-[#101d39] border border-white/10 rounded-2xl px-4 py-4 text-sm text-white focus:ring-2 focus:ring-primary shadow-xl appearance-none transition-all hover:border-primary/30"
        >
            <option value="webhook_api">Webhook / API Trigger</option>
            <option value="time_based">Time-based Trigger (Scheduled)</option>
            <option value="event_based">Event-based Trigger (System Action)</option>
            <option value="behavior_based">Behavior-based Trigger (User Activity)</option>
            <option value="conditional">Conditional Trigger (Rules)</option>
        </select>
        <span class="material-symbols-outlined absolute right-4 top-4 text-slate-500 pointer-events-none group-hover:text-primary transition-colors">expand_more</span>
    </div>
</div>
