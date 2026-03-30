<div class="pt-4 border-t border-white/5" x-data="{ open: false }">
    <button @click="open = !open" type="button" class="flex w-full items-center justify-between text-slate-400 hover:text-white transition-colors group">
        <div class="flex items-center gap-3">
            <span class="material-symbols-outlined text-lg opacity-60 group-hover:opacity-100 transition-opacity">tune</span>
            <span class="text-sm font-bold text-slate-300 group-hover:text-white transition-colors">Advanced Settings</span>
        </div>
        <span class="material-symbols-outlined transition-transform duration-200" :class="open ? 'rotate-180' : ''">expand_more</span>
    </button>
    
    <div x-show="open" x-collapse x-cloak class="mt-6 space-y-5">
        <label class="flex items-center justify-between group cursor-pointer">
            <span class="text-[11px] font-black uppercase tracking-tight text-slate-400 group-hover:text-slate-200 transition-colors">Track Link Clicks</span>
            <input type="checkbox" wire:model.lazy="nodeConfig.track_link_clicks" class="h-5 w-5 rounded border-white/10 bg-[#101d39] text-primary focus:ring-primary transition-all" />
        </label>
        <label class="flex items-center justify-between group cursor-pointer">
            <span class="text-[11px] font-black uppercase tracking-tight text-slate-400 group-hover:text-slate-200 transition-colors">Media Attachments</span>
            <input type="checkbox" wire:model.lazy="nodeConfig.allow_media_attachments" class="h-5 w-5 rounded border-white/10 bg-[#101d39] text-primary focus:ring-primary transition-all" />
        </label>
    </div>
</div>
