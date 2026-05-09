<div class="flex flex-col min-h-full bg-slate-50 dark:bg-slate-900/50">
    <!-- Header -->
    <div class="px-8 py-6 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800">
        <div class="flex flex-col gap-6">
            <div>
                <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight whitespace-nowrap">Contacts</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 font-medium whitespace-nowrap">Manage your customer relationships and WhatsApp audiences.</p>
            </div>
            <div class="flex items-center gap-3 flex-wrap">
                <button wire:click="exportContacts" class="flex items-center gap-2 px-4 py-2 text-sm font-bold text-slate-700 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-all dark:bg-slate-800 dark:text-slate-200 dark:border-slate-700 dark:hover:bg-slate-700 whitespace-nowrap">
                    <span class="material-symbols-outlined text-[20px]">download</span>
                    Export
                </button>
                <button wire:click="openImportModal" class="flex items-center gap-2 px-4 py-2 text-sm font-bold text-slate-700 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-all dark:bg-slate-800 dark:text-slate-200 dark:border-slate-700 dark:hover:bg-slate-700 whitespace-nowrap">
                    <span class="material-symbols-outlined text-[20px]">upload_file</span>
                    Import
                </button>
                <button wire:click="openCreateModal" class="flex items-center gap-2 px-5 py-2.5 text-sm font-bold text-white bg-primary rounded-xl hover:bg-primary/90 shadow-lg shadow-primary/20 transition-all whitespace-nowrap">
                    <span class="material-symbols-outlined text-[20px]">person_add</span>
                    Add Contact
                </button>
            </div>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 px-8 py-6">
        <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-xl">
                    <span class="material-symbols-outlined text-blue-600 dark:text-blue-400">group</span>
                </div>
                <div>
                    <p class="text-[11px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">Total Contacts</p>
                    <p class="text-2xl font-black text-slate-900 dark:text-white">{{ $stats['total'] }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-green-50 dark:bg-green-900/20 rounded-xl">
                    <span class="material-symbols-outlined text-green-600 dark:text-green-400">check_circle</span>
                </div>
                <div>
                    <p class="text-[11px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">Opted In</p>
                    <p class="text-2xl font-black text-slate-900 dark:text-white">{{ $stats['opted_in'] }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-amber-50 dark:bg-amber-900/20 rounded-xl">
                    <span class="material-symbols-outlined text-amber-600 dark:text-amber-400">forum</span>
                </div>
                <div>
                    <p class="text-[11px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">Recent Chats</p>
                    <p class="text-2xl font-black text-slate-900 dark:text-white">{{ $stats['recent'] }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-red-50 dark:bg-red-900/20 rounded-xl">
                    <span class="material-symbols-outlined text-red-600 dark:text-red-400">block</span>
                </div>
                <div>
                    <p class="text-[11px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">Restricted</p>
                    <p class="text-2xl font-black text-slate-900 dark:text-white">{{ $stats['blocked'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters & Table -->
    <div class="px-8 pb-8 flex flex-col">
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col">
            <!-- Filter Bar -->
            <div class="p-4 border-b border-slate-200 dark:border-slate-800 flex flex-wrap items-center gap-4">
                <div class="relative flex-1 min-w-[300px]">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[20px]">search</span>
                    <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search by name, phone or email..." class="w-full pl-10 pr-4 py-2 bg-slate-50 dark:bg-slate-800 border-none rounded-xl text-sm focus:ring-2 focus:ring-primary/20 transition-all dark:text-white">
                </div>
                
                <select wire:model.live="groupId" class="bg-slate-50 dark:bg-slate-800 border-none rounded-xl text-sm px-4 py-2 focus:ring-2 focus:ring-primary/20 dark:text-white">
                    <option value="">All Groups</option>
                    @foreach($groups as $group)
                        <option value="{{ $group->id }}">{{ $group->name }}</option>
                    @endforeach
                </select>

                <select wire:model.live="status" class="bg-slate-50 dark:bg-slate-800 border-none rounded-xl text-sm px-4 py-2 focus:ring-2 focus:ring-primary/20 dark:text-white">
                    <option value="">All Statuses</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="blocked">Blocked</option>
                    <option value="archived">Archived</option>
                </select>
            </div>

            <!-- Table -->
            <div class="no-scrollbar">
                <table class="w-full text-left border-collapse">
                    <thead class="sticky top-0 bg-slate-50 dark:bg-slate-800/50 z-10">
                        <tr>
                            <th class="p-4 w-10">
                                <input type="checkbox" wire:model.live="selectAll" class="rounded border-slate-300 text-primary focus:ring-primary">
                            </th>
                            <th class="p-4 text-[11px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">Contact</th>
                            <th class="p-4 text-[11px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">Groups</th>
                            <th class="p-4 text-[11px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">Source</th>
                            <th class="p-4 text-[11px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">Last Interaction</th>
                            <th class="p-4 text-[11px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">Status</th>
                            <th class="p-4 text-[11px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($contacts as $contact)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                                <td class="p-4">
                                    <input type="checkbox" wire:model.live="selectedContacts" value="{{ $contact->id }}" class="rounded border-slate-300 text-primary focus:ring-primary">
                                </td>
                                <td class="p-4">
                                    <div class="flex items-center gap-3">
                                        <div class="size-10 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 font-bold overflow-hidden">
                                            @if($contact->avatar_url)
                                                <img src="{{ $contact->avatar_url }}" alt="" class="w-full h-full object-cover">
                                            @else
                                                {{ strtoupper(substr($contact->name ?? $contact->phone, 0, 2)) }}
                                            @endif
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-slate-900 dark:text-white">{{ $contact->name ?? 'Unknown' }}</p>
                                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $contact->phone }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-4">
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($contact->groups as $group)
                                            <span class="px-2 py-0.5 rounded-md bg-indigo-50 text-indigo-600 text-[10px] font-bold dark:bg-indigo-900/30 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-800">
                                                {{ $group->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="p-4">
                                    <span class="text-xs font-medium text-slate-600 dark:text-slate-400 capitalize">{{ str_replace('_', ' ', $contact->source) }}</span>
                                </td>
                                <td class="p-4 text-xs text-slate-500 dark:text-slate-400">
                                    {{ $contact->last_interaction_at ? $contact->last_interaction_at->diffForHumans() : 'Never' }}
                                </td>
                                <td class="p-4">
                                    <div class="flex flex-col gap-1">
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider w-fit
                                            {{ $contact->status === 'active' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : '' }}
                                            {{ $contact->status === 'inactive' ? 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-400' : '' }}
                                            {{ $contact->status === 'blocked' ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' : '' }}
                                            {{ $contact->status === 'archived' ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400' : '' }}
                                        ">
                                            {{ $contact->status }}
                                        </span>
                                        @if($contact->do_not_message)
                                            <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-red-50 text-red-600 dark:bg-red-900/20 dark:text-red-400 border border-red-100 dark:border-red-800 w-fit">
                                                NO MESSAGE
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="p-4 text-right">
                                    <div x-data="{ open: false }" class="relative inline-block text-left">
                                        <button @click="open = !open" @click.away="open = false" class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 rounded-lg transition-all" title="Actions">
                                            <span class="material-symbols-outlined text-[20px]">more_vert</span>
                                        </button>
                                        <div x-show="open" 
                                             x-transition:enter="transition ease-out duration-100"
                                             x-transition:enter-start="transform opacity-0 scale-95"
                                             x-transition:enter-end="transform opacity-100 scale-100"
                                             x-transition:leave="transition ease-in duration-75"
                                             x-transition:leave-start="transform opacity-100 scale-100"
                                             x-transition:leave-end="transform opacity-0 scale-95"
                                             class="absolute right-0 z-50 mt-2 w-48 origin-top-right rounded-xl bg-white dark:bg-slate-800 shadow-xl border border-slate-100 dark:border-slate-700 focus:outline-none" 
                                             style="display: none;">
                                            <div class="p-1">
                                                <button wire:click="openEditModal({{ $contact->id }}); open = false" class="flex items-center gap-2 w-full px-3 py-2 text-sm font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 rounded-lg transition-all text-left">
                                                    <span class="material-symbols-outlined text-[18px]">edit</span>
                                                    Edit Contact
                                                </button>
                                                <button wire:click="deleteContact({{ $contact->id }})" wire:confirm="Are you sure you want to delete this contact?" class="flex items-center gap-2 w-full px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-all text-left">
                                                    <span class="material-symbols-outlined text-[18px]">delete</span>
                                                    Delete Contact
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-12 text-center">
                                    <div class="flex flex-col items-center gap-4">
                                        <div class="size-16 rounded-full bg-slate-50 dark:bg-slate-800 flex items-center justify-center">
                                            <span class="material-symbols-outlined text-slate-300 dark:text-slate-600 text-[40px]">group_off</span>
                                        </div>
                                        <div class="max-w-xs">
                                            <p class="text-sm font-bold text-slate-900 dark:text-white">No contacts found</p>
                                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Start by adding a contact manually or importing a CSV file.</p>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="p-4 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-200 dark:border-slate-800">
                {{ $contacts->links() }}
            </div>
        </div>
    </div>

    <!-- Modals -->
    @include('partials.panel.contacts.form-modal')
    @include('partials.panel.contacts.import-modal')
</div>
