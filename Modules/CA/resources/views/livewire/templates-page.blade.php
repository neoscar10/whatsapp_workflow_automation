<div class="p-6 lg:p-8 w-full max-w-7xl mx-auto font-['Inter']">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
        <div>
            <h1 class="text-[24px] font-semibold tracking-tight text-slate-900 dark:text-white">WhatsApp Templates</h1>
            <p class="text-[14px] text-[#424656] dark:text-slate-400 mt-1">Manage and sync WABA message templates with Meta Cloud API.</p>
        </div>
        <div class="flex items-center gap-3">
            <button type="button" wire:click="syncTemplates" 
                class="inline-flex items-center gap-2 px-5 py-2.5 cursor-pointer bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold text-sm transition-all shadow-md active:scale-95">
                <span class="material-symbols-outlined text-[18px]">sync</span>
                Sync from Meta
            </button>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session()->has('success'))
        <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800/30 rounded-xl text-sm text-green-800 dark:text-green-300 flex items-center gap-2">
            <span class="material-symbols-outlined text-[18px]">check_circle</span>
            {{ session('success') }}
        </div>
    @endif
    @if(session()->has('error'))
        <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800/30 rounded-xl text-sm text-red-800 dark:text-red-300 flex items-center gap-2">
            <span class="material-symbols-outlined text-[18px]">error</span>
            {{ session('error') }}
        </div>
    @endif

    <!-- Filters Bar -->
    <div class="bg-white dark:bg-slate-800 border border-[#c2c6d8]/40 dark:border-slate-700 rounded-2xl shadow-sm p-5 mb-8">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
            <!-- Search -->
            <div class="relative">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">search</span>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search templates..." 
                    class="w-full pl-10 pr-4 py-2.5 bg-[#f6f3f2] dark:bg-slate-900 border border-[#c2c6d8]/50 dark:border-slate-700 rounded-xl text-sm focus:border-blue-600 focus:bg-white dark:focus:bg-slate-800 text-slate-900 dark:text-white transition-all">
            </div>

            <!-- Status Filter -->
            <select wire:model.live="status" class="w-full px-4 py-2.5 bg-[#f6f3f2] dark:bg-slate-900 border border-[#c2c6d8]/50 dark:border-slate-700 rounded-xl text-sm focus:border-blue-600 text-slate-900 dark:text-white transition-all">
                <option value="">All Statuses</option>
                <option value="approved">Approved</option>
                <option value="pending">Pending</option>
                <option value="rejected">Rejected</option>
                <option value="draft">Draft</option>
            </select>

            <!-- Category Filter -->
            <select wire:model.live="category" class="w-full px-4 py-2.5 bg-[#f6f3f2] dark:bg-slate-900 border border-[#c2c6d8]/50 dark:border-slate-700 rounded-xl text-sm focus:border-blue-600 text-slate-900 dark:text-white transition-all">
                <option value="">All Categories</option>
                <option value="utility">Utility</option>
                <option value="marketing">Marketing</option>
                <option value="authentication">Authentication</option>
            </select>

            <!-- Language Filter -->
            <select wire:model.live="language" class="w-full px-4 py-2.5 bg-[#f6f3f2] dark:bg-slate-900 border border-[#c2c6d8]/50 dark:border-slate-700 rounded-xl text-sm focus:border-blue-600 text-slate-900 dark:text-white transition-all">
                <option value="">All Languages</option>
                <option value="en_us">English (US)</option>
                <option value="hi">Hindi</option>
            </select>
        </div>
    </div>

    <!-- Templates Grid -->
    @if($templates->isEmpty())
        <div class="text-center py-16 bg-white dark:bg-slate-800 rounded-2xl border border-dashed border-slate-300 dark:border-slate-700">
            <span class="material-symbols-outlined text-[48px] text-slate-400 mb-2">dashboard_customize</span>
            <h3 class="font-bold text-slate-900 dark:text-white text-base">No Templates Found</h3>
            <p class="text-sm text-[#424656] dark:text-slate-400 mt-1">Try modifying your search query or filters.</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($templates as $tmpl)
                @php
                    $status = strtolower($tmpl->status);
                    $badgeClass = match($status) {
                        'approved', 'active' => 'bg-green-50 text-green-700 dark:bg-green-900/20 dark:text-green-400 border-green-500/20',
                        'rejected' => 'bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-400 border-red-500/20',
                        'pending', 'submitted' => 'bg-amber-50 text-amber-700 dark:bg-amber-900/20 dark:text-amber-400 border-amber-500/20',
                        default => 'bg-slate-50 text-slate-700 dark:bg-slate-900/20 dark:text-slate-400 border-slate-500/20',
                    };
                @endphp
                <div class="bg-white dark:bg-slate-800 border border-[#c2c6d8]/40 dark:border-slate-700 rounded-2xl shadow-sm flex flex-col justify-between overflow-hidden hover:shadow-md transition-all">
                    <!-- Top Bar -->
                    <div class="p-5 border-b border-[#c2c6d8]/20 dark:border-slate-700/50 flex items-center justify-between">
                        <div>
                            <span class="inline-block text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ $tmpl->category }}</span>
                            <h3 class="font-bold text-[#1c1b1b] dark:text-white text-base truncate max-w-[180px] mt-0.5" title="{{ $tmpl->display_title }}">{{ $tmpl->display_title }}</h3>
                        </div>
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold uppercase tracking-wider border {{ $badgeClass }}">
                            {{ $tmpl->status }}
                        </span>
                    </div>

                    <!-- Message Body Preview -->
                    <div class="p-5 flex-1 flex flex-col justify-between gap-4">
                        <div class="bg-slate-50 dark:bg-slate-900/40 p-4 rounded-xl border border-slate-100 dark:border-slate-900 font-sans text-sm space-y-2">
                            @if(!empty($tmpl->header_text))
                                <div class="font-bold text-slate-800 dark:text-slate-200">{{ $tmpl->header_text }}</div>
                            @endif
                            <div class="text-slate-600 dark:text-slate-300 leading-relaxed whitespace-pre-line">{{ $tmpl->body_text }}</div>
                            @if(!empty($tmpl->footer_text))
                                <div class="text-xs text-slate-400 mt-2">{{ $tmpl->footer_text }}</div>
                            @endif
                        </div>

                        <!-- Variable mapping guide -->
                        <div class="text-[11px] text-[#727687] dark:text-slate-400">
                            <span class="font-bold">Variables Map:</span> 
                            {{ '{{1}}' }}: Client Name, {{ '{{2}}' }}: Firm Name, {{ '{{3}}' }}: Doc, {{ '{{4}}' }}: Date, {{ '{{5}}' }}: Days, {{ '{{6}}' }}: Upload URL.
                        </div>
                    </div>

                    <!-- Action Bar -->
                    <div class="p-5 bg-slate-50 dark:bg-slate-800/50 border-t border-[#c2c6d8]/20 dark:border-slate-700/50 flex items-center justify-between gap-3">
                        <span class="text-xs font-semibold text-slate-400">Lang: {{ strtoupper($tmpl->language_code) }}</span>
                        
                        <div class="flex items-center gap-2">
                            <button type="button" wire:click="editTemplate({{ $tmpl->id }})" 
                                class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-white dark:bg-slate-700 hover:bg-slate-50 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-600 rounded-lg text-xs font-semibold shadow-sm transition-all">
                                <span class="material-symbols-outlined text-[14px]">edit_note</span>
                                Edit / Resubmit
                            </button>
                            <button type="button" wire:click="deleteTemplate({{ $tmpl->id }})" wire:confirm="Are you sure you want to delete this template from WABA and locally?"
                                class="p-1.5 text-red-500 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-all" title="Delete">
                                <span class="material-symbols-outlined text-[18px]">delete</span>
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-8">
            {{ $templates->links() }}
        </div>
    @endif

    <!-- Edit/Resubmit Modal -->
    @if($showEditModal)
        <div class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm animate-fade-in">
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-2xl overflow-hidden border border-[#c2c6d8]/50 dark:border-slate-700 flex flex-col max-h-[90vh]">
                <!-- Modal Header -->
                <div class="px-6 py-4 border-b border-[#c2c6d8]/50 dark:border-slate-700 flex items-center justify-between bg-slate-50 dark:bg-slate-800/50">
                    <h3 class="text-lg font-bold text-[#1c1b1b] dark:text-white flex items-center gap-2">
                        <span class="material-symbols-outlined text-blue-600 dark:text-blue-400">edit_note</span>
                        Edit & Resubmit Template to Meta
                    </h3>
                    <button type="button" wire:click="$set('showEditModal', false)" class="text-slate-400 hover:text-slate-600 transition-colors">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6 overflow-y-auto flex-1 space-y-6">
                    @if($modalError)
                        <div class="p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800/30 rounded-xl text-sm text-red-800 dark:text-red-300">
                            {{ $modalError }}
                        </div>
                    @endif

                    <div class="space-y-4">
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Header Title</label>
                            <input type="text" wire:model="editTitle" 
                                class="w-full px-4 py-2.5 bg-white dark:bg-slate-800 border border-[#c2c6d8]/50 dark:border-slate-700 rounded-xl text-sm text-[#1c1b1b] dark:text-white focus:border-blue-600">
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Body Text</label>
                            <textarea wire:model="editBody" rows="6"
                                class="w-full px-4 py-3 bg-white dark:bg-slate-800 border border-[#c2c6d8]/50 dark:border-slate-700 rounded-xl text-sm text-[#1c1b1b] dark:text-white focus:border-blue-600 font-mono resize-none"></textarea>
                            <p class="text-[10px] text-[#727687] dark:text-slate-500 mt-1.5">Note: Must use Meta numbered parameters: <code class="font-bold text-slate-700 dark:text-slate-300">{{ '{{1}}' }}</code> for client name, <code class="font-bold text-slate-700 dark:text-slate-300">{{ '{{2}}' }}</code> for firm, etc.</p>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="px-6 py-4 border-t border-[#c2c6d8]/50 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex justify-end gap-3">
                    <button type="button" wire:click="$set('showEditModal', false)" class="px-6 py-2.5 cursor-pointer rounded-xl border border-[#c2c6d8]/50 dark:border-slate-700 hover:bg-white dark:hover:bg-slate-700 transition-colors font-semibold text-[#424656] dark:text-slate-300">
                        Cancel
                    </button>
                    <button type="button" wire:click="saveTemplate" class="px-8 py-2.5 cursor-pointer bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 transition-colors shadow-sm flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px]">publish</span>
                        Submit to Meta
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
