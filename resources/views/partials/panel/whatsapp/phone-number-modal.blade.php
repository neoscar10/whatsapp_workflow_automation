@if ($showNumberModal)
    <div class="relative z-[9999]" aria-labelledby="modal-title" role="dialog" aria-modal="true" wire:keydown.escape.window="closeNumberModal">
        
        {{-- Background backdrop --}}
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"
             wire:click="closeNumberModal"></div>

            <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    {{-- Modal panel --}}
                <div class="relative transform overflow-hidden rounded-xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-slate-200 dark:border-slate-800 dark:bg-slate-900">
                        
                        <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50/50 px-6 py-4 dark:border-slate-800 dark:bg-slate-800/30">
                            <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100" id="modal-title">
                                {{ $editingNumberId ? 'Edit Phone Number' : 'Add Phone Number' }}
                            </h3>

                            <button type="button"
                                    wire:click="closeNumberModal"
                                    class="text-slate-400 transition-colors hover:text-slate-600 dark:hover:text-slate-200">
                                <span class="material-symbols-outlined">close</span>
                            </button>
                        </div>

                        <div class="space-y-5 p-6">
                            @if ($errors->has('phone_numbers_modal'))
                                <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                                    {{ $errors->first('phone_numbers_modal') }}
                                </div>
                            @endif

                            <div class="space-y-2">
                                <label for="display-name" class="block text-sm font-semibold text-slate-700 dark:text-slate-300">
                                    Display Name
                                </label>
                                <input id="display-name"
                                       type="text"
                                       wire:model.defer="display_name"
                                       placeholder="e.g. Customer Support"
                                       class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-slate-900 outline-none transition-all placeholder:text-slate-400 focus:border-transparent focus:ring-2 focus:ring-primary dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100" />
                                <p class="text-xs text-slate-500 dark:text-slate-400">
                                    The name that identifies this number in the panel.
                                </p>
                                @error('display_name')
                                    <p class="text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="space-y-2">
                                <label for="phone-id" class="block text-sm font-semibold text-slate-700 dark:text-slate-300">
                                    Phone Number ID
                                </label>
                                <input id="phone-id"
                                       type="text"
                                       wire:model.defer="phone_number_id"
                                       placeholder="Enter 15-digit ID from Meta Dashboard"
                                       class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 font-mono text-sm text-slate-900 outline-none transition-all placeholder:text-slate-400 focus:border-transparent focus:ring-2 focus:ring-primary dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100" />
                                <div class="flex items-start gap-2 text-primary/80">
                                    <span class="material-symbols-outlined mt-0.5 text-sm">info</span>
                                    <p class="text-xs">Found in your App Dashboard under WhatsApp &gt; Getting Started.</p>
                                </div>
                                @error('phone_number_id')
                                    <p class="text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="flex flex-col justify-end gap-3 bg-slate-50 px-6 py-4 dark:bg-slate-800/50 sm:flex-row">
                            <button type="button"
                                    wire:click="closeNumberModal"
                                    class="rounded-lg px-5 py-2 font-semibold text-slate-600 transition-colors hover:bg-slate-200 dark:text-slate-400 dark:hover:bg-slate-700">
                                Cancel
                            </button>

                            <button type="button"
                                    wire:click="saveNumber"
                                    wire:loading.attr="disabled"
                                    class="rounded-lg bg-primary px-8 py-2 font-bold text-white shadow-md transition-all hover:bg-primary/90 disabled:cursor-not-allowed disabled:opacity-70">
                                <span wire:loading.remove wire:target="saveNumber">
                                    {{ $editingNumberId ? 'Save Changes' : 'Save Number' }}
                                </span>
                                <span wire:loading wire:target="saveNumber">
                                    Saving...
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            </div>
        </div>
    </div>
@endif
