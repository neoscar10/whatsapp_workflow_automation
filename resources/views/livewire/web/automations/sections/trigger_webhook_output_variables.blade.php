<div class="space-y-4 pt-4 border-t border-white/5">
    <div class="flex items-center justify-between">
        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Output Variables</label>
        <span class="text-[9px] font-black bg-white/5 px-2 py-0.5 rounded text-slate-500 uppercase tracking-tighter">
            {{ count($nodeConfig['detected_variables'] ?? []) }} Detected
        </span>
    </div>

    @if(empty($nodeConfig['detected_variables']))
        <div class="p-8 rounded-2xl bg-white/[0.02] border border-dashed border-white/10 flex flex-col items-center justify-center text-center space-y-3 group hover:border-primary/30 transition-all cursor-pointer" wire:click="sendTestEvent">
            <div class="w-12 h-12 rounded-full bg-white/5 flex items-center justify-center group-hover:bg-primary/10 transition-colors">
                <span class="material-symbols-outlined text-slate-600 group-hover:text-primary transition-colors text-2xl">sensors</span>
            </div>
            <div class="space-y-1">
                <p class="text-[11px] font-black text-slate-400 group-hover:text-white transition-colors uppercase tracking-tight">No variables detected yet</p>
                <p class="text-[9px] font-bold text-slate-600 uppercase tracking-tighter">Run a test request to automatically map fields.</p>
            </div>
            <button type="button" class="px-5 py-2 bg-primary/10 border border-primary/20 rounded-xl text-[9px] font-black text-primary uppercase tracking-widest hover:bg-primary hover:text-white transition-all">
                Send Test Event
            </button>
        </div>
    @else
        <div class="space-y-2">
            @foreach($nodeConfig['detected_variables'] as $var)
                <div class="flex items-center justify-between p-3.5 bg-[#101d39]/40 border border-white/5 rounded-2xl group hover:border-primary/20 transition-all">
                    <div class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-sm text-slate-500 group-hover:text-primary transition-colors">settings_input_component</span>
                        <span class="text-xs font-black text-slate-300 group-hover:text-white transition-colors">{{ $var['key'] }}</span>
                    </div>
                    <span class="text-[9px] font-black text-slate-600 group-hover:text-slate-400 tracking-tighter">{{ $var['type'] }}</span>
                </div>
            @endforeach
            <button 
                type="button" 
                wire:click="sendTestEvent"
                class="w-full py-3 bg-white/5 border border-white/10 rounded-xl text-[9px] font-black text-slate-500 uppercase tracking-widest hover:bg-primary/5 hover:text-primary hover:border-primary/30 transition-all"
            >
                Refresh Variables
            </button>
        </div>
    @endif
</div>
