<div class="p-6 border-t border-white/10 bg-[#0a1630]">
    <div class="space-y-3">
        <button 
            wire:click="saveNodeConfig"
            class="w-full py-4 bg-primary text-white text-[11px] font-black uppercase tracking-[0.2em] rounded-2xl hover:bg-primary-dark shadow-[0_10px_20px_rgba(var(--color-primary-rgb),0.2)] transition-all active:scale-95"
        >
            Save {{ ucfirst($activeNode->type) }}
        </button>
        
        <div class="flex gap-3">
            <button 
                wire:click="$set('selectedNodeId', null)"
                class="flex-1 py-3 bg-white/5 border border-white/10 text-slate-400 text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-white/10 transition-colors"
            >
                Cancel
            </button>
            <button 
                wire:click="deleteNode"
                wire:confirm="Are you sure you want to delete this node?"
                class="px-4 py-3 bg-red-500/10 border border-red-500/20 text-red-500 rounded-xl hover:bg-red-500 hover:text-white transition-all group"
                title="Delete Node"
            >
                <span class="material-symbols-outlined text-lg group-hover:scale-110 transition-transform">delete</span>
            </button>
        </div>
    </div>
</div>
