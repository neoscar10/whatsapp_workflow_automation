<div class="p-8 space-y-6 flex-1 overflow-y-auto">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">Companies Management</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Platform companies directory overview.</p>
        </div>
    </div>

    @if (session()->has('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    <!-- Table Container -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-700 text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        <th class="p-4">Company Name</th>
                        <th class="p-4">Owner</th>
                        <th class="p-4">Status</th>
                        <th class="p-4">User Count</th>
                        <th class="p-4">Created Date</th>
                        <th class="p-4">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-sm font-medium">
                    @forelse($companies as $company)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/50 transition-colors">
                            <td class="p-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex size-9 items-center justify-center rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 font-bold uppercase">
                                        {{ substr($company->name, 0, 2) }}
                                    </div>
                                    <div>
                                        <p class="text-slate-900 dark:text-slate-100 font-semibold">{{ $company->name }}</p>
                                        @if($company->slug)
                                            <p class="text-[11px] text-slate-400 mt-0.5 font-bold">@slug({{ $company->slug }})</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="p-4 text-slate-600 dark:text-slate-400">
                                {{ $company->users->first()?->name ?? 'N/A' }}
                                @if($company->users->first()?->email)
                                    <p class="text-[11px] text-slate-400 mt-0.5">{{ $company->users->first()?->email }}</p>
                                @endif
                            </td>
                            <td class="p-4">
                                @php
                                    $statusColors = [
                                        'active' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300',
                                        'trial' => 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300',
                                        'suspended' => 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300',
                                        'demo' => 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300',
                                    ];
                                    $badgeClass = $statusColors[$company->status] ?? 'bg-slate-100 text-slate-800 dark:bg-slate-950 dark:text-slate-300';
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold capitalize {{ $badgeClass }}">
                                    {{ $company->status }}
                                </span>
                            </td>
                            <td class="p-4 text-slate-600 dark:text-slate-400">
                                {{ $company->users_count }} users
                            </td>
                            <td class="p-4 text-slate-600 dark:text-slate-400">
                                {{ $company->created_at->format('Y-m-d H:i') }}
                            </td>
                            <td class="p-4 relative" x-data="{ open: false }">
                                <button @click="open = !open" class="flex items-center justify-center p-1 rounded-full text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                                    <span class="material-symbols-outlined text-[20px]">more_vert</span>
                                </button>
                                <div x-show="open" @click.outside="open = false" x-transition class="absolute right-4 mt-1 w-48 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-lg z-35 py-1" style="display: none;">
                                    <button wire:click="viewCompany({{ $company->id }})" @click="open = false" class="w-full text-left px-4 py-2.5 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 flex items-center gap-2">
                                        <span class="material-symbols-outlined text-[16px]">visibility</span>
                                        <span>View Details</span>
                                    </button>
                                    <button wire:click="openStatusModal({{ $company->id }})" @click="open = false" class="w-full text-left px-4 py-2.5 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 flex items-center gap-2">
                                        <span class="material-symbols-outlined text-[16px]">edit</span>
                                        <span>Manage Status</span>
                                    </button>
                                    <button wire:click="confirmImpersonate({{ $company->id }})" @click="open = false" class="w-full text-left px-4 py-2.5 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 flex items-center gap-2">
                                        <span class="material-symbols-outlined text-[16px]">key</span>
                                        <span>Impersonate Company</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-slate-400 dark:text-slate-500 font-bold">
                                No companies found on the platform.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($companies->hasPages())
            <div class="p-4 border-t border-slate-100 dark:border-slate-850">
                {{ $companies->links() }}
            </div>
        @endif
    </div>

    <!-- View Modal -->
    @if($showViewModal && $selectedCompany)
        <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-lg shadow-xl overflow-hidden animate-in fade-in zoom-in-95 duration-200">
                <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center bg-slate-50 dark:bg-slate-900/50">
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Company Details</h3>
                    <button wire:click="closeModals" class="text-slate-400 hover:text-slate-500 dark:hover:text-slate-300">
                        <span class="material-symbols-outlined text-[20px]">close</span>
                    </button>
                </div>
                <div class="p-6 space-y-4">
                    <div class="flex items-center gap-4">
                        <div class="flex size-14 items-center justify-center rounded-xl bg-primary/10 text-primary font-bold uppercase text-lg">
                            {{ substr($selectedCompany->name, 0, 2) }}
                        </div>
                        <div>
                            <h4 class="text-lg font-bold text-slate-900 dark:text-white">{{ $selectedCompany->name }}</h4>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Slug: {{ $selectedCompany->slug }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 pt-4 border-t border-slate-100 dark:border-slate-800 text-sm">
                        <div>
                            <span class="text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase block">Primary Email</span>
                            <span class="text-slate-700 dark:text-slate-300 font-semibold">{{ $selectedCompany->primary_email ?? 'N/A' }}</span>
                        </div>
                        <div>
                            <span class="text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase block">Status</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold capitalize bg-primary/10 text-primary mt-1">
                                {{ $selectedCompany->status }}
                            </span>
                        </div>
                        <div>
                            <span class="text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase block">Demo Credits</span>
                            <span class="text-slate-700 dark:text-slate-300 font-semibold">₹{{ number_format($selectedCompany->demo_credits ?? 0.0, 2) }}</span>
                        </div>
                        <div>
                            <span class="text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase block">Total Users</span>
                            <span class="text-slate-700 dark:text-slate-300 font-semibold">{{ $selectedCompany->users_count }}</span>
                        </div>
                        @if($selectedCompany->status === 'demo')
                        <div>
                            <span class="text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase block">Demo Phone Number</span>
                            <span class="text-slate-700 dark:text-slate-300 font-semibold">
                                {{ $selectedCompany->demoPhoneNumber ? ($selectedCompany->demoPhoneNumber->display_name . ' (' . $selectedCompany->demoPhoneNumber->phone_number . ')') : 'None assigned' }}
                            </span>
                        </div>
                        <div>
                            <span class="text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase block">Demo Ends At</span>
                            <span class="text-slate-700 dark:text-slate-300 font-semibold">
                                {{ $selectedCompany->demo_ends_at ? $selectedCompany->demo_ends_at->format('Y-m-d H:i') : 'Never' }}
                            </span>
                        </div>
                        @endif
                    </div>
                </div>
                <div class="px-6 py-4 bg-slate-50 dark:bg-slate-900/50 border-t border-slate-100 dark:border-slate-800 flex justify-end">
                    <button wire:click="closeModals" class="px-4 py-2 text-xs font-bold bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-750 text-slate-700 dark:text-slate-300 rounded-lg transition-colors">
                        Close
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Status Modal -->
    @if($showStatusModal && $editingCompany)
        <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-md shadow-xl overflow-hidden animate-in fade-in zoom-in-95 duration-200">
                <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center bg-slate-50 dark:bg-slate-900/50">
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Manage Status - {{ $editingCompany->name }}</h3>
                    <button wire:click="closeModals" class="text-slate-400 hover:text-slate-500 dark:hover:text-slate-300">
                        <span class="material-symbols-outlined text-[20px]">close</span>
                    </button>
                </div>
                <form wire:submit.prevent="saveStatus">
                    <div class="p-6 space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-2">Select Status</label>
                            <select wire:model.live="newStatus" class="w-full h-11 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 pl-4 pr-10 text-sm font-medium text-slate-900 dark:text-white focus:ring-4 focus:ring-primary/10 transition-all outline-none appearance-none" style="background-image: url('data:image/svg+xml,%3csvg xmlns=\'http://www.w3.org/2000/svg\' fill=\'none\' viewBox=\'0 0 20 20\'%3e%3cpath stroke=\'%236b7280\' stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'1.5\' d=\'M6 8l4 4 4-4\'/%3e%3c/svg%3e'); background-position: right 1rem center; background-repeat: no-repeat; background-size: 1.25em 1.25em;">
                                <option value="active">Active</option>
                                <option value="suspended">Suspended</option>
                                <option value="demo">Demo Mode</option>
                            </select>
                            @error('newStatus') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        @if($newStatus === 'demo')
                            <div class="space-y-4 animate-in slide-in-from-top-4 duration-200">
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-2">Demo Credits (₹)</label>
                                    <input type="number" step="0.01" min="0" wire:model="demoCredits" class="w-full h-11 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 px-4 text-sm font-medium text-slate-900 dark:text-white focus:ring-4 focus:ring-primary/10 transition-all outline-none" placeholder="100.00" />
                                    @error('demoCredits') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-2">Demo WhatsApp Phone Number</label>
                                    <select wire:model="selectedDemoPhoneNumberId" class="w-full h-11 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 pl-4 pr-10 text-sm font-medium text-slate-900 dark:text-white focus:ring-4 focus:ring-primary/10 transition-all outline-none appearance-none" style="background-image: url('data:image/svg+xml,%3csvg xmlns=\'http://www.w3.org/2000/svg\' fill=\'none\' viewBox=\'0 0 20 20\'%3e%3cpath stroke=\'%236b7280\' stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'1.5\' d=\'M6 8l4 4 4-4\'/%3e%3c/svg%3e'); background-position: right 1rem center; background-repeat: no-repeat; background-size: 1.25em 1.25em;">
                                        <option value="">Select a Demo Phone Number</option>
                                        @foreach($demoPhoneNumbers as $phone)
                                            <option value="{{ $phone->id }}">{{ $phone->display_name }} ({{ $phone->phone_number }})</option>
                                        @endforeach
                                    </select>
                                    @error('selectedDemoPhoneNumberId') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-2">Demo Duration</label>
                                    <div class="flex gap-2">
                                        <input type="number" min="1" wire:model="demoDuration" class="flex-1 h-11 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 px-4 text-sm font-medium text-slate-900 dark:text-white focus:ring-4 focus:ring-primary/10 transition-all outline-none" />
                                        <select wire:model="demoDurationUnit" class="w-28 h-11 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 pl-4 pr-10 text-sm font-medium text-slate-900 dark:text-white focus:ring-4 focus:ring-primary/10 transition-all outline-none appearance-none" style="background-image: url('data:image/svg+xml,%3csvg xmlns=\'http://www.w3.org/2000/svg\' fill=\'none\' viewBox=\'0 0 20 20\'%3e%3cpath stroke=\'%236b7280\' stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'1.5\' d=\'M6 8l4 4 4-4\'/%3e%3c/svg%3e'); background-position: right 1rem center; background-repeat: no-repeat; background-size: 1.25em 1.25em;">
                                            <option value="days">Days</option>
                                            <option value="hours">Hours</option>
                                            <option value="mins">Mins</option>
                                        </select>
                                    </div>
                                    @error('demoDuration') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                                    @error('demoDurationUnit') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="px-6 py-4 bg-slate-50 dark:bg-slate-900/50 border-t border-slate-100 dark:border-slate-800 flex justify-end gap-3">
                        <button type="button" wire:click="closeModals" class="px-4 py-2 text-xs font-bold bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-750 text-slate-700 dark:text-slate-300 rounded-lg transition-colors">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 text-xs font-bold bg-primary hover:bg-primary-dark text-white rounded-lg transition-colors">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Impersonate Confirmation Modal -->
    @if($showImpersonateModal && $impersonateCompany)
        <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-md shadow-xl overflow-hidden animate-in fade-in zoom-in-95 duration-200">
                <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center bg-slate-50 dark:bg-slate-900/50">
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Confirm Impersonation</h3>
                    <button wire:click="closeModals" class="text-slate-400 hover:text-slate-500 dark:hover:text-slate-300">
                        <span class="material-symbols-outlined text-[20px]">close</span>
                    </button>
                </div>
                <div class="p-6 space-y-3">
                    <p class="text-sm text-slate-600 dark:text-slate-400">
                        Are you sure you want to impersonate <strong class="font-bold">{{ $impersonateCompany->name }}</strong>?
                    </p>
                    <p class="text-xs text-slate-400 dark:text-slate-500">
                        You will be logged in as the company owner. You can exit and return to the Super Admin panel at any time.
                    </p>
                </div>
                <div class="px-6 py-4 bg-slate-50 dark:bg-slate-900/50 border-t border-slate-100 dark:border-slate-800 flex justify-end gap-3">
                    <button type="button" wire:click="closeModals" class="px-4 py-2 text-xs font-bold bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-750 text-slate-700 dark:text-slate-300 rounded-lg transition-colors">
                        Cancel
                    </button>
                    <button type="button" wire:click="impersonate" class="px-4 py-2 text-xs font-bold bg-primary hover:bg-primary-dark text-white rounded-lg transition-colors">
                        Impersonate
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
