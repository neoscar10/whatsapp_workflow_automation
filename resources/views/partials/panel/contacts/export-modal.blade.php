@if ($showExportModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
         wire:keydown.escape.window="closeExportModal">
        
        <div class="fixed inset-0" wire:click="closeExportModal"></div>
        
        <div class="bg-white dark:bg-slate-900 w-full max-w-lg rounded-3xl shadow-2xl overflow-hidden flex flex-col relative z-10"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0">
            
            <!-- Header -->
            <div class="px-8 py-6 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">
                        {{ $exportMode === 'template' ? 'Download Import Template' : 'Export Contacts' }}
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-0.5">
                        Choose your preferred file format before downloading.
                    </p>
                </div>
                <button wire:click="closeExportModal" class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <!-- Content / Format Selection -->
            <div class="p-8 space-y-4">
                <label class="text-[11px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">File Format</label>
                
                <div class="grid grid-cols-1 gap-4">
                    <!-- Excel Option -->
                    <label class="relative flex items-start p-4 rounded-2xl border-2 cursor-pointer transition-all
                        {{ $exportFormat === 'xlsx' 
                            ? 'border-primary bg-primary/5 dark:bg-primary/10 shadow-md shadow-primary/10' 
                            : 'border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700 bg-white dark:bg-slate-800/50' }}">
                        <input type="radio" wire:model.live="exportFormat" value="xlsx" class="sr-only">
                        <div class="p-3 bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 rounded-xl mr-4 shrink-0">
                            <span class="material-symbols-outlined text-[24px]">description</span>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-bold text-slate-900 dark:text-white">Excel Spreadsheet (.xlsx)</span>
                                <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300 rounded text-[10px] font-black uppercase">Recommended</span>
                            </div>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
                                Formatted specifically for Microsoft Excel & Google Sheets. Keeps phone numbers as clean text without scientific notation.
                            </p>
                        </div>
                    </label>

                    <!-- CSV Option -->
                    <label class="relative flex items-start p-4 rounded-2xl border-2 cursor-pointer transition-all
                        {{ $exportFormat === 'csv' 
                            ? 'border-primary bg-primary/5 dark:bg-primary/10 shadow-md shadow-primary/10' 
                            : 'border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700 bg-white dark:bg-slate-800/50' }}">
                        <input type="radio" wire:model.live="exportFormat" value="csv" class="sr-only">
                        <div class="p-3 bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 rounded-xl mr-4 shrink-0">
                            <span class="material-symbols-outlined text-[24px]">article</span>
                        </div>
                        <div class="flex-1">
                            <span class="text-sm font-bold text-slate-900 dark:text-white">CSV File (.csv)</span>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
                                Universal comma-separated text file compatible with all databases, CRM integrations, and custom automation scripts.
                            </p>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Footer -->
            <div class="px-8 py-6 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-3">
                <button wire:click="closeExportModal" class="px-6 py-2.5 text-sm font-bold text-slate-500 hover:text-slate-700 transition-colors">
                    Cancel
                </button>
                <button wire:click="downloadExport" class="px-8 py-2.5 text-sm font-bold text-white bg-primary rounded-xl hover:bg-primary/90 shadow-lg shadow-primary/20 transition-all flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">download</span>
                    Download {{ strtoupper($exportFormat) }}
                </button>
            </div>
        </div>
    </div>
@endif
