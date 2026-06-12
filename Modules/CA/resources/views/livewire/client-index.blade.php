<div class="p-6 lg:p-8 w-full max-w-7xl mx-auto font-['Inter']">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
        <div>
            <h1 class="text-[24px] font-semibold tracking-tight text-slate-900 dark:text-white">Client Directory</h1>
            <p class="text-[14px] text-[#424656] dark:text-slate-400 mt-1">Manage your compliance clients and workspaces.</p>
        </div>
        <a href="{{ route('ca.clients.onboard') }}" class="inline-flex items-center gap-2 px-6 py-2.5 bg-primary hover:bg-primary/90 text-white rounded-lg font-medium text-[14px] transition-all shadow-md active:scale-95">
            <span class="material-symbols-outlined text-[20px]">add</span>
            Onboard New Client
        </a>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-slate-800 border border-[#c2c6d8] dark:border-slate-700 rounded-2xl p-6 shadow-sm mb-8">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
            <div class="md:col-span-6 relative">
                <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-[#727687] dark:text-slate-400">search</span>
                <input type="text" wire:model.live.debounce.300ms="search" class="w-full pl-12 pr-4 py-3 bg-[#f6f3f2] dark:bg-slate-900 border-none rounded-xl focus:ring-2 focus:ring-primary/20 text-[14px] text-[#1c1b1b] dark:text-white transition-all placeholder:text-[#727687] dark:placeholder:text-slate-500" placeholder="Search by name, email, or phone...">
            </div>
            <div class="md:col-span-3">
                <select wire:model.live="business_type_filter" class="w-full px-4 py-3 bg-[#f6f3f2] dark:bg-slate-900 border-none rounded-xl focus:ring-2 focus:ring-primary/20 text-[14px] text-[#1c1b1b] dark:text-white transition-all appearance-none cursor-pointer">
                    <option value="">All Business Types</option>
                    @foreach($businessTypes as $type)
                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-3">
                <select wire:model.live="status_filter" class="w-full px-4 py-3 bg-[#f6f3f2] dark:bg-slate-900 border-none rounded-xl focus:ring-2 focus:ring-primary/20 text-[14px] text-[#1c1b1b] dark:text-white transition-all appearance-none cursor-pointer">
                    <option value="">All Statuses</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col overflow-hidden">
        <div class="overflow-x-auto no-scrollbar">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="p-4 text-[11px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">Client</th>
                        <th class="p-4 text-[11px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">Business Type</th>
                        <th class="p-4 text-[11px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">Email</th>
                        <th class="p-4 text-[11px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">Compliances</th>
                        <th class="p-4 text-[11px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">Status</th>
                        <th class="p-4 text-[11px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($clients as $client)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                            <td class="p-4">
                                <div class="flex items-center gap-3">
                                    <div class="size-10 rounded-xl bg-primary/10 dark:bg-primary/20 flex items-center justify-center text-primary dark:text-blue-400 font-bold overflow-hidden">
                                        {{ substr($client->client_name, 0, 1) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-slate-900 dark:text-white">{{ $client->client_name }}</p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ $client->phone ?? '-' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="p-4">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-slate-100 text-slate-700 text-[11px] font-semibold dark:bg-slate-800 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                    {{ $client->businessType->name ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="p-4 text-sm text-slate-600 dark:text-slate-400">
                                {{ $client->email ?? '-' }}
                            </td>
                            <td class="p-4">
                                <span class="text-xs font-semibold text-primary dark:text-blue-400 bg-primary/10 dark:bg-primary/20 px-2.5 py-1 rounded-lg">
                                    {{ $client->clientCompliances->count() }} Active
                                </span>
                            </td>
                            <td class="p-4">
                                @if($client->status === 'active')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 flex items-center gap-1.5 w-fit">
                                        <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Active
                                    </span>
                                @elseif($client->status === 'draft')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400 flex items-center gap-1.5 w-fit">
                                        <span class="w-1.5 h-1.5 rounded-full bg-orange-500"></span> Draft
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400 flex items-center gap-1.5 w-fit">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> {{ ucfirst($client->status) }}
                                    </span>
                                @endif
                            </td>
                            <td class="p-4 text-right">
                                @if($client->status === 'draft')
                                    <a href="{{ route('ca.clients.onboard', ['draft_id' => $client->id]) }}" class="inline-flex p-2 text-slate-400 hover:text-orange-500 dark:hover:text-orange-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition-all" title="Resume Onboarding">
                                        <span class="material-symbols-outlined text-[20px]">edit_document</span>
                                    </a>
                                @else
                                    <a href="{{ route('ca.clients.show', $client->id) }}" class="inline-flex p-2 text-slate-400 hover:text-primary dark:hover:text-blue-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition-all" title="View Profile">
                                        <span class="material-symbols-outlined text-[20px]">arrow_forward</span>
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-12 text-center">
                                <div class="flex flex-col items-center gap-4">
                                    <div class="size-16 rounded-2xl bg-slate-50 dark:bg-slate-800 flex items-center justify-center">
                                        <span class="material-symbols-outlined text-slate-300 dark:text-slate-600 text-[32px]">search_off</span>
                                    </div>
                                    <div class="max-w-xs">
                                        <p class="text-sm font-bold text-slate-900 dark:text-white">No Clients Found</p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 mb-4">Adjust your search or filters to find what you're looking for.</p>
                                        <button wire:click="$set('search', '')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-lg font-medium text-[13px] transition-all">
                                            Clear Search
                                        </button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($clients->hasPages())
            <div class="p-4 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-200 dark:border-slate-800">
                {{ $clients->links() }}
            </div>
        @endif
    </div>
</div>

