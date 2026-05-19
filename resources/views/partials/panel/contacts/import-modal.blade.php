@if ($showImportModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
         wire:keydown.escape.window="closeModals">
        
        <div class="fixed inset-0" wire:click="closeModals"></div>
        
        <div class="bg-white dark:bg-slate-900 w-full max-w-xl rounded-3xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh] relative z-10"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0">
            
            <!-- Header -->
            <div class="px-8 py-6 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">Import Contacts</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">Upload a CSV file to bulk add or update contacts.</p>
                </div>
                <button wire:click="closeModals" class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <!-- Content -->
            <div class="flex-1 overflow-y-auto p-8 no-scrollbar">
                @if(!$importResults)
                    <div class="space-y-6">
                        <!-- CSV Template Help -->
                        <div class="p-5 bg-primary rounded-2xl border border-primary/20 shadow-lg shadow-primary/10">
                            <div class="flex items-start justify-between mb-3">
                                <p class="text-xs font-black text-white uppercase tracking-widest">Required Columns</p>
                                <button wire:click="downloadImportTemplate" class="text-[10px] font-black uppercase text-white/90 hover:text-white hover:underline flex items-center gap-1 transition-colors">
                                    <span class="material-symbols-outlined text-[14px]">download</span>
                                    Download Sample CSV
                                </button>
                            </div>
                            <p class="text-[11px] text-white/80 leading-relaxed">
                                Your CSV should include: <code class="bg-white/20 px-1.5 py-0.5 rounded text-white font-bold">phone</code> (required), <code class="bg-white/20 px-1.5 py-0.5 rounded text-white font-bold">name</code>, <code class="bg-white/20 px-1.5 py-0.5 rounded text-white font-bold">tags</code>, <code class="bg-white/20 px-1.5 py-0.5 rounded text-white font-bold">groups</code>, <code class="bg-white/20 px-1.5 py-0.5 rounded text-white font-bold">notes</code>, <code class="bg-white/20 px-1.5 py-0.5 rounded text-white font-bold">has_opted_in</code>.
                            </p>
                        </div>

                        <!-- File Upload -->
                        <div class="space-y-2">
                            <label class="text-[11px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">Select CSV File</label>
                            <div class="relative group">
                                <input type="file" wire:model="csvFile" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                                <div class="w-full px-8 py-10 border-2 border-dashed border-slate-200 dark:border-slate-700 rounded-2xl flex flex-col items-center justify-center gap-3 group-hover:border-primary/50 transition-all">
                                    <span class="material-symbols-outlined text-slate-300 group-hover:text-primary transition-colors text-[48px]">cloud_upload</span>
                                    @if($csvFile)
                                        <div class="px-4 py-2 bg-primary rounded-xl flex items-center gap-2 shadow-lg shadow-primary/20 animate-in fade-in zoom-in duration-300">
                                            <span class="material-symbols-outlined text-white text-[18px]">description</span>
                                            <p class="text-sm font-bold text-white">{{ $csvFile->getClientOriginalName() }}</p>
                                        </div>
                                    @else
                                        <p class="text-sm font-bold text-slate-900 dark:text-white">Click to upload or drag and drop</p>
                                        <p class="text-xs text-slate-400 italic">CSV files up to 5MB</p>
                                    @endif
                                </div>
                            </div>
                            @error('csvFile') <span class="text-xs text-red-500 font-bold">{{ $message }}</span> @enderror
                        </div>
                    </div>
                @else
                    <!-- Results View -->
                    <div class="space-y-6">
                        <div class="flex items-center gap-4 p-4 bg-green-50 dark:bg-green-900/20 rounded-2xl border border-green-100 dark:border-green-800">
                            <span class="material-symbols-outlined text-green-600 dark:text-green-400 text-[32px]">task_alt</span>
                            <div>
                                <p class="text-sm font-bold text-green-800 dark:text-green-400">Import Completed</p>
                                <p class="text-xs text-green-600/80 dark:text-green-500/80">Processed {{ $importResults['total_rows'] }} rows.</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="p-4 bg-slate-50 dark:bg-slate-800 rounded-xl">
                                <p class="text-[10px] font-black uppercase tracking-wider text-slate-400">Created</p>
                                <p class="text-xl font-black text-slate-900 dark:text-white">{{ $importResults['created'] }}</p>
                            </div>
                            <div class="p-4 bg-slate-50 dark:bg-slate-800 rounded-xl">
                                <p class="text-[10px] font-black uppercase tracking-wider text-slate-400">Updated</p>
                                <p class="text-xl font-black text-slate-900 dark:text-white">{{ $importResults['updated'] }}</p>
                            </div>
                        </div>

                        @if(!empty($importResults['errors']))
                            <div class="space-y-2">
                                <p class="text-[11px] font-black uppercase tracking-wider text-red-500">Errors ({{ count($importResults['errors']) }})</p>
                                <div class="max-h-40 overflow-y-auto p-4 bg-red-50 dark:bg-red-900/10 rounded-xl border border-red-100 dark:border-red-900/30 text-[11px] text-red-600 dark:text-red-400 space-y-1 no-scrollbar">
                                    @foreach($importResults['errors'] as $error)
                                        <p>• {{ $error }}</p>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Footer -->
            <div class="px-8 py-6 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-3">
                @if($importResults)
                    <button wire:click="closeModals" class="px-8 py-2.5 text-sm font-bold text-white bg-primary rounded-xl hover:bg-primary/90 shadow-lg shadow-primary/20 transition-all">Done</button>
                @else
                    <button wire:click="closeModals" class="px-6 py-2.5 text-sm font-bold text-slate-500 hover:text-slate-700 transition-colors">Cancel</button>
                    <button wire:click="importContacts" wire:loading.attr="disabled" class="px-8 py-2.5 text-sm font-bold text-white bg-primary rounded-xl hover:bg-primary/90 shadow-lg shadow-primary/20 transition-all flex items-center gap-2">
                        <span wire:loading wire:target="importContacts" class="animate-spin size-4 border-2 border-white/30 border-t-white rounded-full"></span>
                        Start Import
                    </button>
                @endif
            </div>
        </div>
    </div>
@endif
