<div class="flex items-center gap-2 {{ $isLowBalance ? 'bg-red-50 border-red-200 dark:bg-red-900/20 dark:border-red-900/30' : 'bg-slate-100 dark:bg-slate-800 border-slate-200 dark:border-slate-700 hover:border-primary/30' }} rounded-xl px-4 py-2 border shadow-sm transition-all relative overflow-hidden group/wallet">
    
    @if($isLowBalance)
        <!-- Pulse effect for low balance -->
        <div class="absolute inset-0 bg-red-400/10 animate-pulse"></div>
    @endif

    <a href="{{ route('wallet.index') }}" wire:navigate class="flex flex-col text-right justify-center relative z-10 hover:opacity-75 transition-opacity cursor-pointer">
        <span class="text-[9px] font-bold uppercase tracking-wider {{ $isLowBalance ? 'text-red-500 dark:text-red-400' : 'text-slate-500 dark:text-slate-400' }} leading-none mb-1">
            {{ $isLowBalance ? 'Low Balance' : 'Wallet Balance' }}
        </span>
        <span class="text-sm font-black {{ $isLowBalance ? 'text-red-600 dark:text-red-500' : 'text-slate-900 dark:text-slate-100' }} leading-none flex items-center gap-1 justify-end">
            {{ $currency === 'USD' ? '$' : '₹' }}{{ number_format($balance, 2) }}
        </span>
    </a>

    <div class="w-px h-6 {{ $isLowBalance ? 'bg-red-200 dark:bg-red-800/50' : 'bg-slate-200 dark:bg-slate-700' }} mx-1 relative z-10"></div>

    <button 
        wire:click="refreshBalance"
        class="group flex items-center justify-center relative z-10 {{ $isLowBalance ? 'text-red-400 hover:text-red-600 hover:bg-red-100 dark:hover:bg-red-900/50' : 'text-slate-400 hover:text-primary hover:bg-white dark:hover:bg-slate-700' }} transition-colors p-1.5 rounded-lg shadow-sm" 
        title="Refresh Balance"
    >
        <span 
            wire:loading.class="animate-spin {{ $isLowBalance ? 'text-red-600' : 'text-primary' }}" 
            wire:target="refreshBalance" 
            class="material-symbols-outlined text-[18px] transition-transform group-hover:rotate-180 duration-500"
        >
            sync
        </span>
    </button>
</div>
