@if ($showFormModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
         wire:keydown.escape.window="closeModals">
        
        <div class="fixed inset-0" wire:click="closeModals"></div>
        
        <div class="bg-white dark:bg-slate-900 w-full max-w-2xl rounded-3xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh] relative z-10"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0">
            
            <!-- Header -->
            <div class="px-8 py-6 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">
                        {{ $contactId ? 'Edit Contact' : 'Add New Contact' }}
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">Enter customer details for WhatsApp automation.</p>
                </div>
                <button wire:click="closeModals" class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <!-- Content -->
            <div class="flex-1 overflow-y-auto p-8 no-scrollbar">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Name -->
                    <div class="space-y-2">
                        <label class="text-[11px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">Full Name</label>
                        <input wire:model="name" type="text" placeholder="e.g. John Doe" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border-none rounded-xl text-sm focus:ring-2 focus:ring-primary/20 transition-all dark:text-white">
                        @error('name') <span class="text-xs text-red-500 font-bold">{{ $message }}</span> @enderror
                    </div>

                    <!-- Phone -->
                    <div class="space-y-2">
                        <label class="text-[11px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">Phone Number (with Country Code)</label>
                        <input wire:model="phone" type="text" placeholder="e.g. 919876543210" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border-none rounded-xl text-sm focus:ring-2 focus:ring-primary/20 transition-all dark:text-white">
                        <p class="text-[10px] text-slate-400 italic">Format: country code + number (no + or spaces)</p>
                        @error('phone') <span class="text-xs text-red-500 font-bold">{{ $message }}</span> @enderror
                    </div>


                    <div class="space-y-2">
                        <label class="text-[11px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">Lifecycle Status</label>
                        <select wire:model="contactStatus" class="w-full pl-4 pr-10 py-3 bg-slate-50 dark:bg-slate-800 border-none rounded-xl text-sm focus:ring-2 focus:ring-primary/20 transition-all dark:text-white appearance-none" style="background-image: url('data:image/svg+xml,%3csvg xmlns=\'http://www.w3.org/2000/svg\' fill=\'none\' viewBox=\'0 0 20 20\'%3e%3cpath stroke=\'%236b7280\' stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'1.5\' d=\'M6 8l4 4 4-4\'/%3e%3c/svg%3e'); background-position: right 0.75rem center; background-repeat: no-repeat; background-size: 1.25em 1.25em;">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="blocked">Blocked</option>
                            <option value="archived">Archived</option>
                        </select>
                        @error('contactStatus') <span class="text-xs text-red-500 font-bold">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="mt-8">
                    <!-- Groups -->
                    <div class="space-y-3">
                        <label class="text-[11px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">Groups</label>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                            @foreach($groups as $group)
                                <label class="flex items-center gap-2 p-2 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800 cursor-pointer transition-colors border border-transparent hover:border-slate-100 dark:hover:border-slate-700">
                                    <input type="checkbox" wire:model="selectedGroups" value="{{ $group->id }}" class="rounded border-slate-300 text-primary focus:ring-primary">
                                    <span class="text-xs font-medium text-slate-600 dark:text-slate-400">{{ $group->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="mt-8 space-y-4 p-5 bg-slate-50 dark:bg-slate-800/50 rounded-2xl border border-slate-100 dark:border-slate-800">
                    <p class="text-[10px] font-black uppercase tracking-wider text-slate-400">Communication Preferences</p>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-green-100 dark:bg-green-900/30 rounded-lg">
                                <span class="material-symbols-outlined text-green-600 dark:text-green-400 text-[18px]">verified_user</span>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-slate-900 dark:text-white">Marketing Opt-in</p>
                                <p class="text-[10px] text-slate-500">Contact has agreed to receive broadcasts.</p>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" wire:model="hasOptedIn" class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary/20 dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary"></div>
                        </label>
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-red-100 dark:bg-red-900/30 rounded-lg">
                                <span class="material-symbols-outlined text-red-600 dark:text-red-400 text-[18px]">notifications_off</span>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-slate-900 dark:text-white">Do Not Message</p>
                                <p class="text-[10px] text-slate-500">Global suppression for all communications.</p>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" wire:model="doNotMessage" class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary/20 dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary"></div>
                        </label>
                    </div>
                </div>

                <!-- Notes -->
                <div class="mt-8 space-y-2">
                    <label class="text-[11px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">Internal Notes</label>
                    <textarea wire:model="notes" rows="3" placeholder="Add any specific context about this customer..." class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border-none rounded-xl text-sm focus:ring-2 focus:ring-primary/20 transition-all dark:text-white no-scrollbar"></textarea>
                </div>
            </div>

            <!-- Footer -->
            <div class="px-8 py-6 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-3">
                <button wire:click="closeModals" class="px-6 py-2.5 text-sm font-bold text-slate-500 hover:text-slate-700 transition-colors">Cancel</button>
                <button wire:click="saveContact" wire:loading.attr="disabled" class="px-8 py-2.5 text-sm font-bold text-white bg-primary rounded-xl hover:bg-primary/90 shadow-lg shadow-primary/20 transition-all flex items-center gap-2">
                    <span wire:loading wire:target="saveContact" class="animate-spin size-4 border-2 border-white/30 border-t-white rounded-full"></span>
                    {{ $contactId ? 'Update Contact' : 'Save Contact' }}
                </button>
            </div>
        </div>
    </div>
@endif
