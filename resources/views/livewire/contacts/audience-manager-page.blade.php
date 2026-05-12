<div class="flex flex-col flex-1 min-h-full bg-slate-50 dark:bg-slate-900/50">
    {{-- Header --}}
    <div class="px-8 py-8 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800">
        <div class="max-w-7xl mx-auto w-full">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <h1 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">Audience Manager</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400 font-medium mt-1">Organize your contacts into lists and groups for targeted messaging.</p>
                </div>
                <div class="flex items-center gap-3">
                    <button wire:click="openStaticGroupModal" class="flex items-center gap-2 px-6 py-3 text-sm font-black text-white bg-primary rounded-2xl hover:bg-primary/90 shadow-lg shadow-primary/20 transition-all">
                        <span class="material-symbols-outlined text-[20px]">person_add</span>
                        New Group
                    </button>
                </div>
            </div>

            {{-- Stats --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
                <div class="bg-slate-50 dark:bg-slate-800/50 p-6 rounded-3xl border border-slate-100 dark:border-slate-800 flex items-center gap-4">
                    <div class="size-12 rounded-2xl bg-indigo-500/10 text-indigo-500 flex items-center justify-center">
                        <span class="material-symbols-outlined text-2xl">group</span>
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Active Groups</p>
                        <p class="text-xl font-black text-slate-900 dark:text-white">{{ $stats['groups_count'] }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Content --}}
    <div class="flex-1 flex flex-col">
        <div class="max-w-7xl mx-auto w-full px-8 py-8 flex flex-col flex-1 min-h-0">
            {{-- Main List Container --}}
            <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] border border-slate-200 dark:border-slate-800 shadow-xl overflow-hidden flex flex-col flex-1 min-h-[500px]">
                {{-- Search Bar --}}
                <div class="px-8 py-6 border-b border-slate-100 dark:border-slate-800">
                    <div class="relative max-w-md">
                        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">search</span>
                        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search groups..." class="w-full pl-12 pr-4 py-3 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl text-sm focus:ring-2 focus:ring-primary/20 transition-all">
                    </div>
                </div>

                {{-- Table --}}
                <div class="flex-1 overflow-y-auto no-scrollbar pb-6">
                    <table class="w-full text-left">
                        <thead class="sticky top-0 bg-white dark:bg-slate-900 z-10">
                            <tr>
                                <th class="px-8 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400 border-b border-slate-100 dark:border-slate-800">Name</th>
                                <th class="px-8 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400 border-b border-slate-100 dark:border-slate-800">Description</th>
                                <th class="px-8 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400 border-b border-slate-100 dark:border-slate-800">Member Count</th>
                                <th class="px-8 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400 border-b border-slate-100 dark:border-slate-800 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50 dark:divide-slate-800/50">
                            @forelse($items as $item)
                                <tr class="group hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-all">
                                    <td class="px-8 py-5">
                                        <div class="flex items-center gap-3">
                                            <span class="text-sm font-black text-slate-900 dark:text-white">{{ $item->name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-8 py-5">
                                        <p class="text-xs text-slate-500 dark:text-slate-400 line-clamp-1">{{ $item->description ?: 'No description provided' }}</p>
                                    </td>
                                    <td class="px-8 py-5">
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-black text-slate-700 dark:text-slate-300">
                                                {{ number_format($item->contacts_count ?? 0) }}
                                            </span>
                                            <span class="text-[10px] font-bold text-slate-400 uppercase">Contacts</span>
                                        </div>
                                    </td>
                                    <td class="px-8 py-5 text-right">
                                        <div class="flex items-center justify-end gap-1">
                                            <button wire:click="openMembershipModal({{ $item->id }})" class="p-2 text-slate-400 hover:text-indigo-500 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 rounded-xl transition-all" title="Manage Members">
                                                <span class="material-symbols-outlined text-[20px]">person_add</span>
                                            </button>
                                            <button wire:click="openStaticGroupModal({{ $item->id }})" class="p-2 text-slate-400 hover:text-primary hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-all" title="Edit Group">
                                                <span class="material-symbols-outlined text-[20px]">edit</span>
                                            </button>
                                            <button wire:click="deleteGroup({{ $item->id }})" wire:confirm="Are you sure you want to delete this group? Contacts will not be deleted." class="p-2 text-slate-400 hover:text-rose-500 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-all" title="Delete Group">
                                                <span class="material-symbols-outlined text-[20px]">delete</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-8 py-20 text-center">
                                        <div class="flex flex-col items-center gap-4">
                                            <div class="size-16 rounded-full bg-slate-50 dark:bg-slate-800 flex items-center justify-center text-slate-300">
                                                <span class="material-symbols-outlined text-[40px]">group_off</span>
                                            </div>
                                            <div>
                                                <p class="text-sm font-black text-slate-900 dark:text-white">No groups found</p>
                                                <p class="text-xs text-slate-500 mt-1">Start by creating a new group to organize your contacts.</p>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Modals --}}
    
    {{-- Static Group Modal --}}
    @if($showStaticGroupModal)
        <div class="fixed inset-0 z-[110] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
            <div class="bg-white dark:bg-slate-900 w-full max-w-lg rounded-[2.5rem] shadow-2xl overflow-hidden flex flex-col relative z-20">
                <div class="px-8 py-6 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <h2 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">{{ $selectedId ? 'Edit Group' : 'Create New Group' }}</h2>
                    <button wire:click="$set('showStaticGroupModal', false)" class="text-slate-400 hover:text-slate-600 transition-colors"><span class="material-symbols-outlined">close</span></button>
                </div>
                <div class="p-8 space-y-6">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase tracking-widest text-slate-400">Group Name</label>
                        <input wire:model="name" type="text" placeholder="e.g. VIP List" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl text-sm focus:ring-2 focus:ring-primary/20 transition-all">
                        @error('name') <p class="text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase tracking-widest text-slate-400">Description</label>
                        <textarea wire:model="description" rows="3" placeholder="What kind of contacts are in this list?" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl text-sm focus:ring-2 focus:ring-primary/20 transition-all"></textarea>
                    </div>
                </div>
                <div class="px-8 py-6 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-100 dark:border-slate-800 flex justify-end gap-3">
                    <button wire:click="$set('showStaticGroupModal', false)" class="px-6 py-2.5 text-xs font-black uppercase tracking-widest text-slate-400 hover:text-slate-600 transition-all">Cancel</button>
                    <button wire:click="saveStaticGroup" class="px-8 py-2.5 text-xs font-black uppercase tracking-widest text-white bg-primary rounded-2xl shadow-lg shadow-primary/20 hover:scale-[1.02] active:scale-[0.98] transition-all">Save Group</button>
                </div>
            </div>
        </div>
    @endif

    {{-- Membership Modal --}}
    @if($showMembershipModal && $membershipGroup)
        <div class="fixed inset-0 z-[110] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
            <div class="bg-white dark:bg-slate-900 w-full max-w-5xl h-[700px] rounded-[2.5rem] shadow-2xl overflow-hidden flex flex-col relative z-20 max-h-[95vh]">
                <div class="px-8 py-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between shrink-0">
                    <div>
                        <h2 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">Manage Members</h2>
                        <p class="text-[10px] text-slate-500 font-medium">Add or remove contacts for <strong>{{ $membershipGroup->name }}</strong> ({{ number_format($membershipGroup->contacts_count) }} members)</p>
                    </div>
                    <button wire:click="$set('showMembershipModal', false)" class="text-slate-400 hover:text-slate-600 transition-colors"><span class="material-symbols-outlined">close</span></button>
                </div>

                <div class="flex-1 overflow-hidden flex flex-col md:flex-row">
                    {{-- Left Panel: Available Contacts --}}
                    <div class="flex-1 border-r border-slate-100 dark:border-slate-800 flex flex-col overflow-hidden">
                        <div class="p-6 border-b border-slate-50 dark:border-slate-800/50">
                            <h3 class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-4">Add New Members</h3>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">search</span>
                                <input wire:model.live.debounce.300ms="availableSearch" type="text" placeholder="Search contacts to add..." class="w-full pl-10 pr-4 py-2.5 bg-slate-50 dark:bg-slate-800 border-none rounded-xl text-xs focus:ring-2 focus:ring-primary/20 transition-all">
                            </div>
                        </div>

                        <div class="flex-1 overflow-y-auto p-6 no-scrollbar">
                            <div class="space-y-2">
                                @forelse($availableContacts as $contact)
                                    <label class="flex items-center gap-3 p-3 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800/50 cursor-pointer transition-all border border-transparent hover:border-slate-100 dark:hover:border-slate-700">
                                        <input type="checkbox" wire:model.live="selectedContactIds" value="{{ $contact->id }}" class="rounded border-slate-300 text-primary focus:ring-primary size-4">
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-bold text-slate-900 dark:text-white truncate">{{ $contact->name ?: 'Unknown' }}</p>
                                            <p class="text-[10px] text-slate-500 font-medium">{{ $contact->phone }}</p>
                                        </div>
                                    </label>
                                @empty
                                    <div class="py-12 text-center">
                                        <p class="text-xs text-slate-400 font-medium">No available contacts found.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        @if($availableContacts->hasPages())
                            <div class="px-6 py-4 border-t border-slate-50 dark:border-slate-800/50">
                                {{ $availableContacts->links() }}
                            </div>
                        @endif
                    </div>

                    {{-- Right Panel: Current Members --}}
                    <div class="flex-1 flex flex-col overflow-hidden bg-slate-50/30 dark:bg-slate-800/20">
                        <div class="p-6 border-b border-slate-50 dark:border-slate-800/50">
                            <h3 class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-4">Current Members</h3>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">search</span>
                                <input wire:model.live.debounce.300ms="memberSearch" type="text" placeholder="Filter current members..." class="w-full pl-10 pr-4 py-2.5 bg-white dark:bg-slate-900 border-none rounded-xl text-xs focus:ring-2 focus:ring-primary/20 transition-all">
                            </div>
                        </div>

                        <div class="flex-1 overflow-y-auto p-6 no-scrollbar">
                            <div class="space-y-2">
                                @forelse($currentMembers as $member)
                                    <div class="flex items-center gap-3 p-3 rounded-xl bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 group/item">
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-bold text-slate-900 dark:text-white truncate">{{ $member->name ?: 'Unknown' }}</p>
                                            <p class="text-[10px] text-slate-500 font-medium">{{ $member->phone }}</p>
                                        </div>
                                        <button wire:click="removeMember({{ $member->id }})" wire:confirm="Remove this contact from the group?" class="p-2 text-slate-400 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/20 rounded-lg transition-all opacity-0 group-hover/item:opacity-100">
                                            <span class="material-symbols-outlined text-[18px]">person_remove</span>
                                        </button>
                                    </div>
                                @empty
                                    <div class="py-12 text-center">
                                        <p class="text-xs text-slate-400 font-medium">No members found in this group.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        @if($currentMembers->hasPages())
                            <div class="px-6 py-4 border-t border-slate-50 dark:border-slate-800/50">
                                {{ $currentMembers->links() }}
                            </div>
                        @endif
                    </div>
                </div>

                <div class="px-8 py-4 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between shrink-0">
                    <p class="text-xs font-bold text-slate-500">
                        @if(count($selectedContactIds) > 0)
                            <span class="text-primary">{{ count($selectedContactIds) }}</span> contacts selected
                        @else
                            No contacts selected
                        @endif
                    </p>
                    <div class="flex gap-3">
                        <button wire:click="$set('showMembershipModal', false)" class="px-6 py-2.5 text-xs font-black uppercase tracking-widest text-slate-400 hover:text-slate-600 transition-all">Close</button>
                        <button 
                            wire:click="addSelectedContacts" 
                            wire:loading.attr="disabled"
                            @if(empty($selectedContactIds)) disabled @endif
                            class="px-8 py-2.5 text-xs font-black uppercase tracking-widest text-white bg-primary rounded-2xl shadow-lg shadow-primary/20 hover:scale-[1.02] active:scale-[0.98] transition-all disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100 flex items-center gap-2"
                        >
                            <span wire:loading wire:target="addSelectedContacts" class="animate-spin size-3 border-2 border-white/30 border-t-white rounded-full"></span>
                            Add Members
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
