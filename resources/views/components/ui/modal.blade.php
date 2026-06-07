@props(['title', 'maxWidth' => 'max-w-lg'])

<div 
    x-data="{ show: @entangle($attributes->wire('model')) }"
    x-show="show"
    x-on:keydown.escape.window="show = false"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
    role="dialog"
    aria-modal="true"
    x-cloak
>
    <!-- Background backdrop click close -->
    <div class="fixed inset-0" x-on:click="show = false"></div>
    
    <!-- Modal panel content wrapper -->
    <div 
        class="bg-white dark:bg-slate-900 w-full {{ $maxWidth }} rounded-3xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh] relative z-10"
        x-show="show"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-95 translate-y-4"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
    >
        <!-- Header -->
        <div class="px-8 py-6 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <div>
                <h2 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">
                    {{ $title }}
                </h2>
            </div>
            <button x-on:click="show = false" class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <!-- Content -->
        <div class="flex-1 overflow-y-auto p-8 no-scrollbar">
            {{ $slot }}
        </div>
    </div>
</div>
